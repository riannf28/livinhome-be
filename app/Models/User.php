<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Chat\ChatDetails;
use App\Models\Transaction\Cart;
use App\Models\Transaction\Survey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fullname',
        'gender',
        'date_of_birth',
        'phone_number',
        'job',
        'city',
        'status',
        'last_education',
        'emergency_contact',
        'id_card',
        'id_card_with_person',
        'photo_profile',
        'roles',
        'email',
        'password',
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
        'password' => 'hashed',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function chat_detail()
    {
        return $this->belongsTo(ChatDetails::class);
    }

    public function rating_property()
    {
        return $this->belongsTo(RatingProperty::class);
    }
}
