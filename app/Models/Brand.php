<?php

namespace App\Models;

use App\Models\Contact;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model {

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'name',
        'photo',
        'description',
        'status'
    ];

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function contact() {
        return $this->belongsTo(Contact::class);
    }
}
