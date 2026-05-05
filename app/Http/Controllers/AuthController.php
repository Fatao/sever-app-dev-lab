<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\DTO\UserDTO;
use App\DTO\AuthSuccessDTO;
use App\DTO\TokenListDTO;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Token;
use App\Services\Interfaces\TokenServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'username' => $data['username'],
            'name'     => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'birthday' => $data['birthday'],
        ]);

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday->format('Y-m-d')
        );

        return response()->json($userDTO->toArray(), 201);
    }

    /**
     * Login user and issue tokens.
     */
    /**
     * Handle user login. Returns temp token if 2FA is enabled.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // If 2FA is enabled, return a temporary token instead
        if ($user->two_factor_enabled) {
            $clientSignature = hash(
                'sha256',
                $request->ip() . $request->userAgent() . config('app.key')
            );

            $tempToken = app(\App\Services\Interfaces\TemporaryTokenServiceInterface::class)
                ->generate($user, $clientSignature);

            return response()->json([
                'message'    => '2FA required',
                'temp_token' => $tempToken,
                'expires_in' => 300,
            ], 200);
        }

        $authDTO = $this->tokenService->generateTokens($user);

        return response()->json($authDTO->toArray(), 200);
    }

    /**
     * Get info about the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday->format('Y-m-d')
        );

        return response()->json($userDTO->toArray(), 200);
    }

    /**
     * Logout by revoking the current access token.
     */
    public function out(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token) {
            $this->tokenService->revoke($token);
        }

        return response()->json(['message' => 'Logged out.'], 200);
    }

    /**
     * Revoke all tokens of the current user.
     */
    public function outAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->tokenService->revokeAll($user);

        return response()->json(['message' => 'Logged out from all devices.'], 200);
    }

    /**
     * Get metadata of all active tokens for the current user.
     */
    public function tokens(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeTokens = Token::where('user_id', $user->id)
            ->where('revoked', false)
            ->get()
            ->map(fn ($t) => [
                'id'           => $t->id,
                'type'         => $t->type,
                'created_at'   => $t->created_at,
                'expires_at'   => $t->expires_at,
                'last_used_at' => $t->last_used_at,
                'ip_address'   => $t->ip_address,
            ])
            ->toArray();

        $dto = new TokenListDTO($activeTokens);

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Refresh access token using a valid refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token required.'], 422);
        }

        try {
            $authDTO = $this->tokenService->refresh($refreshToken);

            return response()->json($authDTO->toArray(), 200);
        } catch (\Exception $e) {
            if ($e->getCode() === 401) {
                $user = $request->user() ?? null;

                if ($user) {
                    $this->tokenService->revokeAll($user);
                }
            }

            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Change the authenticated user's password and revoke all tokens.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 422);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $this->tokenService->revokeAll($user);

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}