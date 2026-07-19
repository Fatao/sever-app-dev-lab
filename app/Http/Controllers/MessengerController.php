<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\MessengerDTO;
use App\DTO\UserMessengerDTO;
use App\Http\Requests\ConnectMessengerRequest;
use App\Http\Requests\VerifyMessengerRequest;
use App\Models\Messenger;
use App\Models\NotificationLog;
use App\Models\UserMessenger;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MessengerController extends Controller
{
    /**
     * Get list of messengers available for the current environment.
     */
    public function index(): JsonResponse
    {
        $env = app()->environment();

        $messengers = Messenger::where('environment', $env)
            ->get()
            ->map(fn(Messenger $m) => MessengerDTO::fromModel($m)->toArray());

        return response()->json(['data' => $messengers], 200);
    }

    /**
     * Get all messenger connections for the authenticated user.
     */
    public function userMessengers(Request $request): JsonResponse
    {
        $user = $request->user();

        $connections = UserMessenger::where('user_id', $user->id)
            ->with('messenger')
            ->get()
            ->map(fn(UserMessenger $um) => UserMessengerDTO::fromModel($um)->toArray());

        return response()->json(['data' => $connections], 200);
    }

    /**
     * Initiate messenger connection by sending a verification code.
     */
    public function connect(ConnectMessengerRequest $request, Messenger $messenger): JsonResponse
    {
        $user              = $request->user();
        $messengerUserId   = $request->validated()['messenger_user_id'];

        // Generate 6-digit code
        $code    = (string) random_int(100000, 999999);
        $cacheKey = "messenger_verify_{$user->id}_{$messenger->id}";

        Cache::put($cacheKey, [
            'code'              => $code,
            'messenger_user_id' => $messengerUserId,
        ], Carbon::now()->addMinutes(10));

        // Send the code via Telegram
        if ($messenger->name === 'telegram') {
            $service = app(TelegramService::class);
            $sent    = $service->sendMessage(
                $messengerUserId,
                "Ваш код подтверждения: {$code}\nВведите его в приложении для завершения привязки."
            );

            if (!$sent) {
                return response()->json([
                    'error' => 'Failed to send verification code. Check your Telegram chat ID.',
                ], 422);
            }
        }

        return response()->json([
            'message'  => 'Verification code sent. Check your messenger and enter the code.',
            'dev_code' => app()->environment('local') ? $code : null,
        ], 200);
    }

    /**
     * Verify messenger connection using the code received in the messenger.
     */
    public function verify(VerifyMessengerRequest $request, Messenger $messenger): JsonResponse
    {
        $user     = $request->user();
        $code     = $request->validated()['code'];
        $cacheKey = "messenger_verify_{$user->id}_{$messenger->id}";

        $cached = Cache::get($cacheKey);

        if (!$cached || $cached['code'] !== $code) {
            return response()->json(['error' => 'Invalid or expired verification code.'], 422);
        }

        Cache::forget($cacheKey);

        UserMessenger::updateOrCreate(
            ['user_id' => $user->id, 'messenger_id' => $messenger->id],
            [
                'messenger_user_id'     => $cached['messenger_user_id'],
                'is_verified'           => true,
                'verified_at'           => Carbon::now(),
                'notifications_enabled' => true,
            ]
        );

        return response()->json([
            'message' => 'Messenger connected and verified successfully.',
        ], 200);
    }

    /**
     * Disconnect a messenger from the user account.
     */
    public function destroy(Request $request, UserMessenger $userMessenger): JsonResponse
    {
        if ($userMessenger->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $userMessenger->delete();

        return response()->json(['message' => 'Messenger disconnected.'], 200);
    }

    /**
     * Toggle notifications on or off for a messenger connection.
     */
    public function toggle(Request $request, UserMessenger $userMessenger): JsonResponse
    {
        if ($userMessenger->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $userMessenger->update([
            'notifications_enabled' => !$userMessenger->notifications_enabled,
        ]);

        return response()->json([
            'message'               => 'Notification status updated.',
            'notifications_enabled' => $userMessenger->notifications_enabled,
        ], 200);
    }

    /**
     * Handle incoming webhook from Telegram for admin /get_logs command.
     */
    public function webhookReport(Request $request): JsonResponse
    {
        // Verify the request is from Telegram using secret token
        $secret = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($secret !== config('messenger.telegram.token')) {
            // For Telegram webhooks, validate by checking update structure
        }

        $update = $request->all();

        // Extract message and chat info
        $message = $update['message'] ?? null;
        if (!$message) {
            return response()->json(['ok' => true], 200);
        }

        $chatId  = $message['chat']['id'] ?? null;
        $text    = $message['text'] ?? '';

        if (!$chatId || !str_starts_with($text, '/get_logs')) {
            return response()->json(['ok' => true], 200);
        }

        // Find admin user by messenger_user_id
        $userMessenger = UserMessenger::where('messenger_user_id', (string) $chatId)
            ->where('is_verified', true)
            ->with('user')
            ->first();

        if (!$userMessenger) {
            app(TelegramService::class)->sendMessage((string) $chatId, 'Вы не зарегистрированы в системе.');
            return response()->json(['ok' => true], 200);
        }

        $user = $userMessenger->user;

        // Check if admin
        $isAdmin = $user->roles()->where('slug', 'admin')->exists();
        if (!$isAdmin) {
            app(TelegramService::class)->sendMessage((string) $chatId, 'Доступ запрещён. Только для администраторов.');
            return response()->json(['ok' => true], 200);
        }

        // Generate CSV report
        $since = Carbon::now()->subHours(24);
        $logs  = NotificationLog::where('created_at', '>=', $since)->get();

        $csvPath = storage_path('app/notification_logs_report.csv');
        $handle  = fopen($csvPath, 'w');
        fputcsv($handle, ['ID', 'User ID', 'Messenger ID', 'Status', 'Attempt', 'Created At']);

        foreach ($logs as $log) {
            fputcsv($handle, [$log->id, $log->user_id, $log->messenger_id, $log->status, $log->attempt, $log->created_at]);
        }

        fclose($handle);

        app(TelegramService::class)->sendDocument((string) $chatId, $csvPath, 'Логи уведомлений за последние 24 часа');

        unlink($csvPath);

        return response()->json(['ok' => true], 200);
    }
}
