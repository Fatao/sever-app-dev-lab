<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\UserDTO;
use App\DTO\AuthSuccessDTO;
use App\DTO\TokenListDTO;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Token;
use App\Services\Interfaces\TokenServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService
    ) {}

    // Register a new user
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated(); // validated by FormRequest

        // Create user
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'birthday' => $data['birthday'],
        ]);

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday
        );

        return response()->json($userDTO->toArray(), 201);
    }

    // Login user and issue tokens
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate tokens using your TokenService
        $authDTO = $this->tokenService->generateTokens($user);

        return response()->json($authDTO->toArray(), 200);
    }

    // Get info about current user
    public function me(Request $request): JsonResponse
    {
        $user = $request->user(); // ✅ CHANGED: method call

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday
        );

        return response()->json($userDTO->toArray(), 200);
    }

    // Logout current token
    public function out(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        if ($token) {
            $this->tokenService->revoke($token);
        }

        return response()->json(['message' => 'Logged out'], 200);
    }

    // Revoke all tokens of current user
    public function outAll(Request $request): JsonResponse
    {
        $user = $request->user(); // ✅ CHANGED: method call
        $this->tokenService->revokeAll($user);

        return response()->json(['message' => 'Logged out from all devices'], 200);
    }

    // Get active tokens metadata
    public function tokens(Request $request): JsonResponse
    {
        $user = $request->user(); // ✅ CHANGED: method call

        $activeTokens = Token::where('user_id', $user->id)
            ->where('revoked', false)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'created_at' => $t->created_at,
                'expires_at' => $t->expires_at,
                'last_used_at' => $t->last_used_at,
                'ip_address' => $t->ip_address,
            ])
            ->toArray();

        $dto = new TokenListDTO($activeTokens);

        return response()->json($dto->toArray(), 200);
    }

    // Refresh access token using refresh token
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token required'], 422);
        }

        try {
            $authDTO = $this->tokenService->refresh($refreshToken);
            return response()->json($authDTO->toArray(), 200);
        } catch (\Exception $e) {
            // If token already used or invalid → revoke all tokens
            if ($e->getCode() === 401) {
                $user = $request->user() ?? null; // ✅ CHANGED: method call
                if ($user) {
                    $this->tokenService->revokeAll($user);
                }
            }
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}
