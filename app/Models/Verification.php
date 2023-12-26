<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Verification extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'verification_codes';
    
    protected $fillable = [
        'code',
        'phone',
        'status'
    ];
}
    