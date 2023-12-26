<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model {
    
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'purchase_price',
        'retail_price',
        'deal_price',
        'status'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
