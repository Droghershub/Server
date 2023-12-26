<?php

namespace App\Models;

use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {
    
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'address_id',
        'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function detail() {
        return $this->hasMany(OrderDetail::class);
    }
}