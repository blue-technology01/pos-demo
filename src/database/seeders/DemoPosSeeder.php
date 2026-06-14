<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoPosSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            // ─────────────────────────────
            // 2. CATEGORIES
            // ─────────────────────────────
            $categories = [
            [
                'code' => 'MRT-BEV-01',
                'name' => 'Drinks',
                'description' => 'Carbonated sodas, fresh fruit juices, energy drinks, and bottled water.',
                'image' => 'categories/drinks.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-SNK-02',
                'name' => 'Snacks',
                'description' => 'Chips, biscuits, chocolate, and packaged snacks.',
                'image' => 'categories/snacks.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-FOOD-03',
                'name' => 'Food',
                'description' => 'Instant food, noodles, and ready meals.',
                'image' => 'categories/food.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-MILK-04',
                'name' => 'Dairy',
                'description' => 'Milk, yogurt, cheese products.',
                'image' => 'categories/dairy.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-CLEAN-05',
                'name' => 'Cleaning',
                'description' => 'Soap, detergent, cleaning supplies.',
                'image' => 'categories/cleaning.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-PER-06',
                'name' => 'Personal Care',
                'description' => 'Shampoo, toothpaste, skincare.',
                'image' => 'categories/personal.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-FROZ-07',
                'name' => 'Frozen',
                'description' => 'Frozen food and ice cream.',
                'image' => 'categories/frozen.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-DRY-08',
                'name' => 'Dry Goods',
                'description' => 'Rice, sugar, flour, cooking ingredients.',
                'image' => 'categories/dry.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-TOY-09',
                'name' => 'Toys',
                'description' => 'Kids toys and games.',
                'image' => 'categories/toys.webp',
                'status' => 'active',
            ],
            [
                'code' => 'MRT-OTH-10',
                'name' => 'Other',
                'description' => 'Miscellaneous products.',
                'image' => 'categories/other.webp',
                'status' => 'active',
            ],
        ];

            DB::table('categories')->insert(array_map(fn($c) => [
                'code' => $c['code'],
                'name' => $c['name'],
                'description' => $c['description'],
                'image' => $c['image'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ], $categories));


            // ─────────────────────────────
            // 3. PRODUCTS (10 records)
            // ─────────────────────────────
            $products = [
            [
                'product_code' => 'PRD-DRK-001',
                'name' => 'Coca Cola 500ml',
                'code' => 'MRT-BEV-01',
                'cost_price' => 0.80,
                'selling_price' => 1.50,
                'stock' => 120,
                'min_stock' => 10,
                'barcode' => '5449000000439',
                'description' => 'Classic Coca Cola carbonated drink.',
                'image' => 'products/coca-cola.webp',
                'expiry_date' => '2027-03-01',
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-DRK-002',
                'name' => 'Pepsi 500ml',
                'code' => 'MRT-BEV-01',
                'cost_price' => 0.70,
                'selling_price' => 1.40,
                'stock' => 110,
                'min_stock' => 10,
                'barcode' => '4890008100309',
                'description' => 'Pepsi refreshing soft drink.',
                'image' => 'products/pepsi.webp',
                'expiry_date' => '2027-05-01',
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-DRK-003',
                'name' => 'Sprite 500ml',
                'code' => 'MRT-BEV-01',
                'cost_price' => 0.70,
                'selling_price' => 1.40,
                'stock' => 90,
                'min_stock' => 10,
                'barcode' => '4900000001111',
                'description' => 'Lemon-lime soft drink.',
                'image' => 'products/sprite.webp',
                'expiry_date' => '2027-05-01',
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-SNK-001',
                'name' => 'Lays Chips Original',
                'code' => 'MRT-SNK-02',
                'cost_price' => 0.50,
                'selling_price' => 1.20,
                'stock' => 150,
                'min_stock' => 20,
                'barcode' => '8850229100012',
                'description' => 'Potato chips original flavor.',
                'image' => 'products/lays.webp',
                'expiry_date' => '2026-12-01',
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-SNK-002',
                'name' => 'Chocolate Bar',
                'code' => 'MRT-SNK-02',
                'cost_price' => 0.60,
                'selling_price' => 1.50,
                'stock' => 130,
                'min_stock' => 15,
                'barcode' => '7622210449283',
                'description' => 'Milk chocolate bar.',
                'image' => 'products/chocolate.webp',
                'expiry_date' => '2027-01-01',
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-MILK-001',
                'name' => 'Fresh Milk 1L',
                'code' => 'MRT-MILK-04',
                'cost_price' => 1.00,
                'selling_price' => 2.00,
                'stock' => 80,
                'min_stock' => 10,
                'barcode' => '8880001112223',
                'description' => 'Fresh cow milk 1 liter.',
                'image' => 'products/milk.webp',
                'expiry_date' => '2026-10-01',
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-CLN-001',
                'name' => 'Dishwashing Liquid',
                'code' => 'MRT-CLEAN-05',
                'cost_price' => 0.60,
                'selling_price' => 1.30,
                'stock' => 60,
                'min_stock' => 5,
                'barcode' => '8850001234567',
                'description' => 'Kitchen cleaning liquid.',
                'image' => 'products/dishwash.webp',
                'expiry_date' => null,
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-DRY-001',
                'name' => 'Rice 5kg',
                'code' => 'MRT-DRY-08',
                'cost_price' => 4.00,
                'selling_price' => 6.50,
                'stock' => 200,
                'min_stock' => 20,
                'barcode' => '8855001110001',
                'description' => 'Premium jasmine rice 5kg.',
                'image' => 'products/rice.webp',
                'expiry_date' => null,
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-TOY-001',
                'name' => 'Toy Car',
                'code' => 'MRT-TOY-09',
                'cost_price' => 1.50,
                'selling_price' => 3.50,
                'stock' => 40,
                'min_stock' => 5,
                'barcode' => '8900001119991',
                'description' => 'Kids toy racing car.',
                'image' => 'products/toy-car.webp',
                'expiry_date' => null,
                'status' => 'active',
            ],
            [
                'product_code' => 'PRD-OTH-001',
                'name' => 'Gift Box',
                'code' => 'MRT-OTH-10',
                'cost_price' => 2.00,
                'selling_price' => 4.50,
                'stock' => 30,
                'min_stock' => 5,
                'barcode' => '9000001118881',
                'description' => 'Multi-purpose gift box.',
                'image' => 'products/gift-box.webp',
                'expiry_date' => null,
                'status' => 'active',
            ],
        ];

            DB::table('products')->insert(array_map(fn($p) => [
                'product_code' => $p[0],
                'name' => $p[1],
                'code' => $p[2],
                'cost_price' => $p[3],
                'selling_price' => $p[4],
                'stock' => rand(50, 200),
                'min_stock' => 10,
                'barcode' => Str::random(12),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ], $products));

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
