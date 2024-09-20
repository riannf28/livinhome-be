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
            $table->string('booking_code')->nullable()->unique();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->foreign('property_id')->references('id')->on('properties');
            $table->string('fullname');
            $table->string('phone_number');
            $table->string('gender');
            $table->string('job');
            $table->string('duration');
            $table->string('marriage');
            $table->integer('number_of_renters');
            $table->string('school_name')->nullable();
            $table->longText('id_card');
            $table->date('checkin');
            $table->longText('additional_note')->nullable();
            $table->boolean('status')->nullable();
            $table->string('bank')->nullable();
            $table->date('payment_date')->nullable();
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
