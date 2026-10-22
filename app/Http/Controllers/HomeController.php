<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->where('active', true)
            ->orderBy('id')
            ->with('category:id,name')
            ->get(['id', 'category_id', 'name', 'image', 'unit', 'origin', 'price', 'salePrice', 'stock'])
            ->map(fn(Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'image' => str_starts_with($product->image, '/images/')
                    ? asset('assets' . $product->image)
                    : $product->image,
                'unit' => $product->unit,
                'origin' => $product->origin,
                'price' => $product->price,
                'salePrice' => $product->salePrice,
                'stock' => $product->stock,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ],
            ]);

        return Inertia::render('Welcome', [
            'products' => $products,
        ]);
    }
}
