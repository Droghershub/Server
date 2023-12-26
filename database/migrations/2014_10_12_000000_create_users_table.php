<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('google')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('guest')->nullable()->unique();
            $table->string('password')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });
        
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'accountant']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'customer']);
        
        $user = User::create([
            'name' => 'Ravindra M',
            'email' => 'balajiravindra2512@yahoo.co.in',
            'phone' => '9008422424',
            'password' => 'admin'
        ]);
        
        $user->assignRole('admin');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
