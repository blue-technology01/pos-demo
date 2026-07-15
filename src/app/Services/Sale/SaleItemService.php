<?php

namespace App\Services\Sale;

use App\Models\SaleItem;
use Illuminate\Support\Str;

class SaleItemService
{
    const SESSION_KEY = 'sale_items';

    public function getItems(): array
    {
        return session(self::SESSION_KEY, []);
    }

    // function for manage add to cart items
    public function addItem(array $data): array
    {
        // get current cart from session
        $items = $this->getItems();

        // Check if the product already exists in the items array
        $existingKey = $this->findItemKey($items, $data['product_code']);

        if ($existingKey !== null) {

            // Merge quantity if product already exists
            $items[$existingKey]['quantity'] += $data['quantity'] ?? 1;

            // Update other fields if provided
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

            // Calculate the amount for the new item
            $item['amount'] = $this->calculateAmount($item);
            // add new item to cart array
            /**
             *  array_push($items, $item)
             *
             *  [
             *      product A,
             *      product B,
             *  ]
             *
             */
            $items[]= $item;
        }

        // store update cart to session
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

    // remove items from cart
    public function removeItem(string $rowId): array
    {
        // retrieve the current cart item from session
        $items = $this->getItems();

        $items = array_values(
            array_filter($items, fn($item) => $item['row_id'] !== $rowId)
        );

        // store update back to the session
        session([self::SESSION_KEY => $items]);

        return $items;
    }

    // function for remove cart from session
    public function clearItems(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    // get summary
    public function getSummary(): array
    {

        $items = $this->getItems();
        // calculate subtotal
        $subtotal = '0.00';
        $totalDiscount = '0.00';
        $total = '0.00';

        foreach( $items as $item ) {

            // item_total : unit_price * qty
            $itemTotal = bcmul((string)$item['unit_price'], (string)$item['quantity'], 2);

            // sub_total : subtotal + item_total
            $subtotal = bcadd($subtotal, $itemTotal, 2);

            // BCMath
            // 0.1+0.2 = 0.3 but actual 0.30000000000000004

            // descount : totalDescount + decount_amount
            $totalDiscount = bcadd($totalDiscount, (string)$item['discount_amount'], 2);

            // total amount : total + amount
            $total = bcadd($total, (string)$item['amount'], 2);

        }

        return [
            'subtotal'       => $subtotal,
            'total_discount' => $totalDiscount,
            'total'          => $total,
            'item_count'     => count($items),
        ];
    }

    // method save all cart items from the session into database after sale created
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

    // method finding item in cart and return array if it exists
    private function findItemKey(array $items, string $productCode): int|null
    {
        foreach ($items as $key => $item) {
            // compare the current item's product_code with product code of item that finding
            if ($item['product_code'] === $productCode) {
                return $key;
            }
        }
        return null;
    }

    // method calculates the final amount of a cart item after applying discounts.
    private function calculateAmount(array $item): float
    {
        // calculate price before descount
        $subtotal = bcmul((string)$item['unit_price'], (string)$item['quantity'], 4);

        // calculate descount
        $discount = (string)($item['discount_amount'] ?? '0');

        if (($item['discount_percentage'] ?? 0) > 0) {
            $percentVal = bcdiv((string)$item['discount_percentage'], '100', 4);
            $discount = bcmul($subtotal, $percentVal, 4);
        }

        // sub_total - descount
        $finalAmount = bcsub($subtotal, $discount, 2);

        // make sure price it not < 0
        return bccomp($finalAmount, '0', 2) === -1 ? '0.00' : $finalAmount;
    }
}
