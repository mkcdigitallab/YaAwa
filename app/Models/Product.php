<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'price',
        'salePrice',
        'stock',
        'version',
        'unit',
        'origin',
        'description',
        'image',
        'active',
        'featured',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'price' => 'integer',
        'salePrice' => 'integer',
        'stock' => 'integer',
        'version' => 'integer',
        'active' => 'boolean',
        'featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
