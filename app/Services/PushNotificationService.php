<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function send(User $user, string $title, string $body): void
    {
        if (! $user->push_token) {
            return;
        }

        Log::info('Push notification queued', [
            'user_id' => $user->id,
            'push_token' => $user->push_token,
            'title' => $title,
            'body' => $body,
        ]);
    }
}
