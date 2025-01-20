<?php

namespace App\Models\Transaction;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'transactions';

    protected $fillable = [
        'booking_code',
        'user_id',
        'property_id',
        'fullname',
        'phone_number',
        'gender',
        'job',
        'duration',
        'marriage',
        'number_of_renters',
        'school_name',
        'id_card',
        'checkin',
        'additional_note',
        'status',
        'is_cancel',
        'proof_of_payment',
        'bank',
        'payment_date',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }

    public function property()
    {
        return $this->hasMany(Property::class, 'id', 'property_id');
    }

    public function additional_features()
    {
        return $this->belongsTo(AdditionalFeatures::class);
    }

    public static function generate_kode()
    {
        $isUnique = false;

        while (!$isUnique) {
            $code = 'LIVIN-' . Str::random(3) . now()->format('dmYHis');

            $existingCode = Transaction::where('booking_code', $code)->first();

            if (!$existingCode) {
                $isUnique = true;
            }
        }

        return $code;
    }

    public function id_card_url() {
        return Storage::url($this->id_card);
    }

    public function proof_of_payment_url() {
        return Storage::url($this->proof_of_payment);
    }

}
