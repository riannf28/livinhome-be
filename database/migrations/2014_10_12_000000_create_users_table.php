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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->boolean('gender');
            $table->date('date_of_birth');
            $table->string('phone_number')->unique();
            $table->string('job')->nullable();
            $table->string('school_name')->nullable();
            $table->string('city')->nullable();
            $table->string('status')->nullable();
            $table->string('last_education')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->longText('photo_profile')->nullable();
            $table->longText('id_card')->nullable();
            $table->longText('id_card_with_person')->nullable();
            $table->string('roles');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
