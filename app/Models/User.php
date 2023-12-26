<?php

namespace App\Models;

use App\Models\Handcart;
use App\Models\Order;
use App\Models\Favorite;
use App\Models\Address;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject {
    
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'photo',
        'email',
        'google',
        'phone',
        'guest',
        'password',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

    // Implement the methods from JWTSubject contract

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }
    
    public function orders() {
        return $this->hasMany(Order::class);
    }
    
    public function handcarts() {
        return $this->hasMany(Handcart::class);
    }
    
    public function favorites() {
        return $this->hasMany(Favorite::class);
    }
    
    public function addresses() {
        return $this->hasMany(Address::class);
    }
    
    public function notifications() {
        return $this->hasMany(Notification::class);
    }
}