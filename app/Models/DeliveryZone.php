<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $table = 'deliveryZones';

    protected $fillable = [
        'name',
        'fee',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'deleveryzone_id');
    }
}
