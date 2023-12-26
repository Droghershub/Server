<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('postcode_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->string('name');
            $table->string('care_of');
            $table->string('phone');
            $table->string('line_1');
            $table->string('line_2');
            $table->boolean('default')->default(false);
            $table->enum('type', ['HOME', 'OFFICE', 'OTHER'])->default('OTHER');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('postcode_id')->references('id')->on('postcodes');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
    