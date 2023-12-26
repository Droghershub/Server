<?php

namespace App\Models;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Postcode extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'code',
        'city',
        'district',
        'state',
        'country',
        'status'
    ];
    
    public function addresses() {
        return $this->hasMany(Address::class);
    }
}