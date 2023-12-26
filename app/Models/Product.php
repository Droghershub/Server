<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Handcart;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {
    
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'photo',
        'description',
        'model',
        'purchase_price',
        'retail_price',
        'current_price',
        'quantity',
        'status'
    ];

    public function brand() {
        return $this->belongsTo(Brand::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
    
    public function handcarts() {
        return $this->hasMany(Handcart::class);
    }
}