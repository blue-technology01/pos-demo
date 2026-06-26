<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Get unread notifications
     */
    public function getUnread(User $user): Collection
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->latest()
            ->get();
    }

    /**
     * Create notification
     */
    public function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $refCode = null
    ): Notification {
        return Notification::create([
            'user_id'  => $userId,
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'ref_code' => $refCode,
            'read_at'  => null,
        ]);
    }

    /**
     * Mark one notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->update([
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }
}
