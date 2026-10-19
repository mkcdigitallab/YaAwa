<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(database_path('data/Demon.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        var_dump($data);
        $timestamp = now();

        User::firstOrCreate(
            ['email' => 'yaa-awa@example.com'],
            [
                'name' => 'Yaa-Awa',
                'password' => Hash::make(env('YAA_AWA_PASSWORD', 'password')),
                'email_verified_at' => $timestamp,
            ],
        );

        $categories = array_map(fn(array $category): array => [
            'id' => (int) $category['id'],
            'name' => $category['name'],
            'color' => $category['color'],
            'image' => $category['image'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $data['categories']);

        DB::table('categories')->upsert(
            $categories,
            ['id'],
            ['name', 'color', 'image', 'updated_at'],
        );

        $products = array_map(fn(array $product): array => [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'category_id' => (int) $product['categoryId'],
            'price' => $product['price'],
            'salePrice' => $product['salePrice'],
            'stock' => $product['stock'],
            'unit' => $product['unit'],
            'origin' => $product['origin'],
            'description' => $product['description'],
            'image' => $product['image'],
            'active' => $product['active'],
            'featured' => $product['featured'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $data['products']);

        DB::table('products')->upsert(
            $products,
            ['id'],
            [
                'name',
                'category_id',
                'price',
                'salePrice',
                'stock',
                'unit',
                'origin',
                'description',
                'image',
                'active',
                'featured',
                'updated_at',
            ],
        );

        $deliveryZones = array_map(fn(array $zone): array => [
            'id' => (int) $zone['id'],
            'name' => $zone['name'],
            'fee' => $zone['fee'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $data['deliveryZones']);

        DB::table('deliveryZones')->upsert(
            $deliveryZones,
            ['id'],
            ['name', 'fee', 'updated_at'],
        );

        foreach (array_slice($data['orders'], 0, 6) as $order) {
            $createdAt = Carbon::parse($order['createdAt'])->toDateTimeString();
            $updatedAt = Carbon::parse($order['updatedAt'])->toDateTimeString();

            DB::table('orders')->upsert([
                [
                    'reference' => $order['reference'],
                    'checkout_tocken' => (string) str()->uuid(),
                    'deleveryzone_id' => (int) $order['customer']['zoneId'],
                    'zone_name' => $order['customer']['zoneName'],
                    'adresse' => $order['customer']['address'],
                    'fulfillmentMode' => $order['fulfillmentMode'],
                    'note' => $order['note'],
                    'sub_total' => $order['subtotal'],
                    'discount_percentage' => $order['discount']['percentage'],
                    'discount_amount' => $order['discount']['amount'],
                    'total' => $order['total'],
                    'statut' => $order['status'],
                    'stock_applied' => $order['stockApplied'],
                    'stock_restored' => $order['stockRestored'],
                    'canceled_at' => $order['status'] === 'ANNULEE' ? $updatedAt : null,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ],
            ], ['reference'], [
                'deleveryzone_id',
                'zone_name',
                'adresse',
                'fulfillmentMode',
                'note',
                'sub_total',
                'discount_percentage',
                'discount_amount',
                'total',
                'statut',
                'stock_applied',
                'stock_restored',
                'canceled_at',
                'updated_at',
            ]);

            $orderId = DB::table('orders')
                ->where('reference', $order['reference'])
                ->value('id');

            $items = array_map(fn(array $item): array => [
                'order_id' => $orderId,
                'products_id' => (int) $item['productId'],
                'name' => $item['name'],
                'unit' => $item['unit'],
                'unit_price' => $item['unitPrice'],
                'quantity' => $item['quantity'],
                'line_total' => $item['lineTotal'],
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ], $order['items']);

            DB::table('orders_items')->upsert(
                $items,
                ['order_id', 'products_id'],
                ['name', 'unit', 'unit_price', 'quantity', 'line_total', 'updated_at'],
            );
        }
    }
}
