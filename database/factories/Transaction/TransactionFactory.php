<?php

namespace Database\Factories\Transaction;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_code' => 'LIVIN-' . Str::random(3) . now()->format('dmYHis'),
            'property_id' => \App\Models\Property::inRandomOrder()->first()->id ?? null, // Pastikan Anda memiliki data di tabel properties
            'fullname' => $this->faker->name,
            'phone_number' => $this->faker->regexify('(628)[0-9]{9,12}'), // Format Indonesia
            'gender' => $this->faker->randomElement(['male', 'female']),
            'job' => $this->faker->jobTitle,
            'duration' => $this->faker->numberBetween(1, 12) . ' months',
            'marriage' => $this->faker->randomElement(['single', 'married', 'divorced']),
            'number_of_renters' => $this->faker->numberBetween(1, 3),
            'school_name' => $this->faker->optional()->company,
            'id_card' => $this->faker->text(200),
            'checkin' => $this->faker->date,
            'additional_note' => $this->faker->optional()->text(500),
            'status' => $this->faker->boolean,
            'bank' => $this->faker->optional()->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB']),
            'payment_date' => $this->faker->optional()->date,
        ];
    }
}
