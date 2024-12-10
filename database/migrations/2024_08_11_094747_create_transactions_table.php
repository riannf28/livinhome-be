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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('booking_code')->nullable()->unique();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->foreign('property_id')->references('id')->on('properties');
            $table->string('fullname');
            $table->string('phone_number');
            $table->string('gender');
            $table->string('job');
            $table->string('duration');
            $table->string('marriage')->nullable();
            $table->integer('number_of_renters');
            $table->string('school_name')->nullable();
            $table->longText('id_card');
            $table->date('checkin');
            $table->longText('additional_note')->nullable();
            $table->boolean('status')->nullable();
            $table->string('bank')->nullable();
            $table->date('payment_date')->nullable();
            $table->boolean('is_cancel')->nullable();
            $table->longText('proof_of_payment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
