<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use App\Models\Postcode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model {

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'postcode_id',
        'name',
        'care_of',
        'phone',
        'line_1',
        'line_2',
        'default',
        'type',
        'status'
    ];

    protected $casts = [
        'default' => 'boolean'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function postcode() {
        return $this->belongsTo(Postcode::class);
    }

}
