<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model {
    
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'payload',
        'photo',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
