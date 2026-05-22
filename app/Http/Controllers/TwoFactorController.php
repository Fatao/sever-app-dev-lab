<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\TwoFactorRequestCodeDTO;
use App\DTO\TwoFactorStatusDTO;
use App\DTO\TwoFactorVerifyDTO;
use App\DTO\UserDTO;
use App\Http\Requests\ToggleTwoFactorRequest;
use App\Http\Requests\VerifyTwoFactorRequest;
use App\Models\User;
use App\Services\Interfaces\TemporaryTokenServiceInterface;
use App\Services\Interfaces\TwoFactorServiceInterface;
use App\Services\Interfaces\TokenServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly TwoFactorServiceInterface      $twoFactorService,
        private readonly TemporaryTokenServiceInterface $tempTokenService,
        private readonly TokenServiceInterface          $tokenService,
    ) {}

    /**
     * Return 2FA status for the authenticated user.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $dto  = new TwoFactorStatusDTO(twoFactorEnabled: (bool) $user->two_factor_enabled);

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Toggle 2FA on or off for the authenticated user.
     */
    public function toggle(ToggleTwoFactorRequest $request): JsonResponse
    {
        $user   = $request->user();
        $data   = $request->validated();
        $enable = filter_var($data['enable'], FILTER_VALIDATE_BOOLEAN);

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Invalid password'], 422);
        }

        if (!$enable) {
            $clientSignature = hash('sha256', $request->ip() . $request->userAgent() . config('app.key'));
            $valid = $this->twoFactorService->verifyCode($user, $clientSignature, $data['code']);

            if (!$valid) {
                return response()->json(['error' => 'Invalid or expired 2FA code'], 422);
            }
        }

        $user->update(['two_factor_enabled' => $enable]);

        return response()->json([
            'message'            => $enable ? '2FA enabled successfully' : '2FA disabled successfully',
            'two_factor_enabled' => $enable,
        ], 200);
    }

    /**
     * Request a new 2FA code (requires temp token middleware).
     */
    public function requestCode(Request $request): JsonResponse
    {
        $user            = $request->user();
        $clientSignature = $request->attributes->get('client_signature');

        $plainCode = $this->twoFactorService->generateCode($user, $clientSignature);

        // In production: send via email/SMS. Here we return it for testing.
        $dto = new TwoFactorRequestCodeDTO(
            message: '2FA code generated. Check your email.',
            code:    $plainCode,
        );

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Verify a 2FA code and return full access tokens on success.
     */
    public function verify(VerifyTwoFactorRequest $request): JsonResponse
    {
        $user            = $request->user();
        $clientSignature = $request->attributes->get('client_signature');
        $tempToken       = $request->attributes->get('temp_token');
        $code            = $request->validated()['code'];

        $valid = $this->twoFactorService->verifyCode($user, $clientSignature, $code);

        if (!$valid) {
            return response()->json(['error' => 'Invalid or expired 2FA code'], 422);
        }

        // Invalidate temp token
        $this->tempTokenService->invalidate($tempToken);

        

        // Generate full tokens
$authDTO = $this->tokenService->generateTokens($user);
$authArray = $authDTO->toArray();

$dto = new TwoFactorVerifyDTO(
    accessToken:  $authArray['access_token'],
    refreshToken: $authArray['refresh_token'],
    user:         UserDTO::fromModel($user),
);

return response()->json($dto->toArray(), 200); 
    }
}   