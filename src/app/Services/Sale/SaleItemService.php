<?php

namespace App\Services\Sale;

use App\Models\Product;
use App\Models\SaleItem;
// use App\Models\SaleItem;

use Illuminate\Support\Str;

class SaleItemService
{
    const SESSION_KEY = 'sale_items';

    public function getItems(): array
    {
        return session(self::SESSION_KEY, []);
    }

    // add or merge items
    public function addItem(array $data): array
    {
        $items = $this->getItems();

        $existingKey = $this->findItemKey($items, $data['product_code']);

        if ($existingKey !== null) {
            // Merge quantity if product already exists
            $items[$existingKey]['quantity'] += $data['quantity'] ?? 1;
            $items[$existingKey]['amount']    = $this->calculateAmount($items[$existingKey]);
        } else {
            // Add new item
            $item = [
                'row_id'               => (string) Str::uuid(),
                'product_code'         => $data['product_code'],
                'product_name'         => $data['product_name'],
                'uom_code'             => $data['uom_code'] ?? null,
                'quantity'             => $data['quantity'] ?? 1,
                'cost_price'           => $data['cost_price'] ?? 0,
                'unit_price'           => $data['unit_price'] ?? 0,
                'discount_percentage'  => $data['discount_percentage'] ?? 0,
                'discount_amount'      => $data['discount_amount'] ?? 0,
                'amount'               => 0,
            ];

            $item['amount'] = $this->calculateAmount($item);
            $items[]        = $item;
        }

        session([self::SESSION_KEY => $items]);

        return $items;
    }

    // update items by row
    public function updateItem(string $rowId, array $data): array
    {
        $items = $this->getItems();

        foreach ($items as &$item) {
            if ($item['row_id'] === $rowId) {
                $item['quantity']             = $data['quantity']            ?? $item['quantity'];
                $item['unit_price']           = $data['unit_price']          ?? $item['unit_price'];
                $item['discount_percentage']  = $data['discount_percentage'] ?? $item['discount_percentage'];
                $item['discount_amount']      = $data['discount_amount']     ?? $item['discount_amount'];
                $item['uom_code']             = $data['uom_code']            ?? $item['uom_code'];
                $item['amount']               = $this->calculateAmount($item);
                break;
            }
        }

        session([self::SESSION_KEY => $items]);

        return $items;
    }
    // remove items by row
    public function removeItem(string $rowId): array
    {
        $items = $this->getItems();

        $items = array_values(
            array_filter($items, fn($item) => $item['row_id'] !== $rowId)
        );

        session([self::SESSION_KEY => $items]);

        return $items;
    }
    public function clearItems(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    // get summary
    public function getSummary(): array
    {
        $items = $this->getItems();

        $subtotal        = collect($items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $totalDiscount   = collect($items)->sum(fn($i) => $i['discount_amount']);
        $total           = collect($items)->sum(fn($i) => $i['amount']);

        return [
            'subtotal'       => round($subtotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'total'          => round($total, 2),
            'item_count'     => count($items),
        ];
    }

    public function persistToDatabase(int $saleId): void
    {
        $items = $this->getItems();

        $rows = array_map(fn($item) => [
            'sale_id'              => $saleId,
            'product_code'         => $item['product_code'],
            'product_name'         => $item['product_name'],
            'uom_code'             => $item['uom_code'],
            'quantity'             => $item['quantity'],
            'cost_price'           => $item['cost_price'],
            'unit_price'           => $item['unit_price'],
            'discount_percentage'  => $item['discount_percentage'],
            'discount_amount'      => $item['discount_amount'],
            'amount'               => $item['amount'],
        ], $items);

        SaleItem::insert($rows);
    }

    private function findItemKey(array $items, string $productCode): int|null
    {
        foreach ($items as $key => $item) {
            if ($item['product_code'] === $productCode) {
                return $key;
            }
        }
        return null;
    }

    private function calculateAmount(array $item): float
    {
        $subtotal = $item['quantity'] * $item['unit_price'];

        // Discount by percentage takes priority if set
        if ($item['discount_percentage'] > 0) {
            $discountAmount = $subtotal * ($item['discount_percentage'] / 100);
        } else {
            $discountAmount = $item['discount_amount'];
        }
        return round($subtotal - $discountAmount, 2);
    }
}
