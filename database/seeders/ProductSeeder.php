<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Steel Widget', 'sku' => 'SKU-STL-001', 'description' => 'Cold-rolled steel widget', 'base_price' => 125.00, 'stock_quantity' => 500, 'is_active' => true],
            ['name' => 'Copper Fitting', 'sku' => 'SKU-COP-002', 'description' => 'Lead-free copper fitting', 'base_price' => 89.50, 'stock_quantity' => 300, 'is_active' => true],
            ['name' => 'Aluminum Bracket', 'sku' => 'SKU-ALU-003', 'description' => 'Lightweight aluminum bracket', 'base_price' => 42.75, 'stock_quantity' => 800, 'is_active' => true],
            ['name' => 'Rubber Gasket', 'sku' => 'SKU-RUB-004', 'description' => 'Heat-resistant rubber gasket', 'base_price' => 12.25, 'stock_quantity' => 2000, 'is_active' => true],
            ['name' => 'Hydraulic Valve', 'sku' => 'SKU-HYD-005', 'description' => 'High-pressure hydraulic valve', 'base_price' => 340.00, 'stock_quantity' => 120, 'is_active' => true],
            ['name' => 'Fastener Kit', 'sku' => 'SKU-FAS-006', 'description' => 'Assorted stainless fastener kit', 'base_price' => 24.99, 'stock_quantity' => 1500, 'is_active' => true],
            ['name' => 'Industrial Sensor', 'sku' => 'SKU-SEN-007', 'description' => 'IP67 industrial proximity sensor', 'base_price' => 210.00, 'stock_quantity' => 200, 'is_active' => true],
            ['name' => 'Discontinued Coupler', 'sku' => 'SKU-DIS-008', 'description' => 'Legacy coupler (no longer stocked)', 'base_price' => 999.00, 'stock_quantity' => 0, 'is_active' => false],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'base_price' => $product['base_price'],
                    'stock_quantity' => $product['stock_quantity'],
                    'is_active' => $product['is_active'],
                ],
            );
        }
    }
}
