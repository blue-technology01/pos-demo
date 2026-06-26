<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\Notification\NotificationService;
use App\Services\Inventory\InventoryNotificationService;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly InventoryNotificationService $inventoryNotificationService
    ) {}

    /**
     * FIXED: Always return array (NOT collection)
     */
    public function fetch(): JsonResponse
    {
        $user = auth()->user();

        $notifications = $this->notificationService->getUnread($user);

        $data = $notifications->map(function ($n) {
            return [
                'id'      => $n->id,
                'type'    => $n->type,
                'title'   => $n->title,
                'message' => $n->message,
                'read'    => (bool) $n->read_at,
            ];
        })->values()->all();

        return response()->json([
            'count' => count($data),
            'data'  => $data,
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $this->notificationService->markAsRead($notification);

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        $this->notificationService->markAllAsRead(auth()->user());

        return back();
    }

    /**
     * FIXED generate (safe + clear)
     */
    public function generate(): RedirectResponse
    {
        $user = auth()->user();

        $expiryProducts = $this->inventoryNotificationService->getExpiryNotifications();
        $lowStockProducts = $this->inventoryNotificationService->getLowStockNotifications();

        foreach ($expiryProducts as $product) {
            $this->notificationService->create(
                $user->id,
                'expiry',
                $product->name,
                'Expires: ' . $product->expiry_date,
                $product->code
            );
        }

        foreach ($lowStockProducts as $product) {
            $this->notificationService->create(
                $user->id,
                'low_stock',
                $product->name,
                "Low stock: {$product->stock}",
                $product->code
            );
        }

        return back()->with('success', 'Notifications generated');
    }
}
