<?php

namespace App\Http\Controllers;

use App\Services\NotificationService\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $service
    ) {}

    /**
     * Return low stock notifications as JSON for polling.
     */
    public function lowStock(): JsonResponse
    {
        $products = $this->service->getLowStockProducts();

        return response()->json([
            'count'    => $products->count(),
            'products' => $products->map(fn ($p) => [
                'code'      => $p->code,
                'name'      => $p->name,
                'stock'     => (float) $p->stock,
                'min_stock' => (float) $p->min_stock,
                'image'     => $p->image ? asset('storage/' . $p->image) : null,
            ]),
        ]);
    }
}
