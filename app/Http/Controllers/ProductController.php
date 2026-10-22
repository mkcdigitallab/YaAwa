<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('active', true)
            ->with('category:id,name')
            ->orderBy('id')
            ->get(['id', 'category_id', 'name', 'description', 'origin', 'unit', 'image', 'price', 'salePrice', 'stock'])
            ->map(fn(Product $product): array => $this->productData($product));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless($product->active, 404);

        return response()->json($this->productData($product->loadMissing('category:id,name')));
    }

    /**
     * @return array{id: int, name: string, description: ?string, origin: ?string, unit: string, image: string, price: int, salePrice: ?int, stock: int, category: array{id: int, name: string}}
     */
    private function productData(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'origin' => $product->origin,
            'unit' => $product->unit,
            'image' => str_starts_with($product->image, '/images/')
                ? asset('assets' . $product->image)
                : $product->image,
            'price' => $product->price,
            'salePrice' => $product->salePrice,
            'stock' => $product->stock,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ],
        ];
    }
}
