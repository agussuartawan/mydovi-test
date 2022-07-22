<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'order_date',
        'order_time',
        'total',
        'cash',
        'change'
    ];

    public function product()
    {
        return $this->belongsToMany(Product::class, 'detail_orders')->withPivot('qty', 'unit_price', 'subtotal');
    }
}
