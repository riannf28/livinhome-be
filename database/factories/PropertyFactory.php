<?php

namespace Database\Factories;

use App\Utils\Constants;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 3,
            'nama' => $this->faker->word,
            'deskripsi' => $this->faker->paragraph,
            'tanggal_dibuat' => $this->faker->date,
            'tanggal_mulai_sewa' => $this->faker->date,
            'sewa_untuk' => $this->faker->randomElement(['Pria', 'Wanita', 'Keduanya']),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'kategori' => $this->faker->randomElement(Constants::$category_property),
            'provinsi' => $this->faker->state,
            'kota' => $this->faker->city,
            'kecamatan' => $this->faker->citySuffix,
            'alamat' => $this->faker->address,
            'catatan_alamat' => $this->faker->sentence,
            'fasilitas' => $this->faker->randomElement(['kosongan', 'furnished', 'semi-furnished']),
            'lebar_tanah' => $this->faker->numberBetween(5, 20),
            'kapasitas_motor' => $this->faker->numberBetween(1, 5),
            'kapasitas_mobil' => $this->faker->numberBetween(1, 5),
            'daya_listrik' => $this->faker->randomElement([450, 900, 1300, 2200]),
            'sumber_air' => $this->faker->randomElement(['PDAM', 'Sumur']),
            'total_kamar' => $this->faker->numberBetween(1, 10),
            'total_lemari' => $this->faker->numberBetween(1, 10),
            'minimum_sewa' => $this->faker->numberBetween(1, 12),
            'meja' => $this->faker->numberBetween(0, 1),
            'kasur' => $this->faker->numberBetween(0, 1),
            'harga_sewa_tahun' => $this->faker->numberBetween(10000000, 50000000),
            'harga_sewa_1_bulan' => $this->faker->numberBetween(1000000, 5000000),
            'harga_sewa_3_bulan' => $this->faker->numberBetween(2500000, 15000000),
            'bank' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'rekening' => $this->faker->bankAccountNumber,
        ];
    }
}
