<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'rating',
    ];

    public function property()
    {
        return $this->hasMany(Property::class, 'id', 'property_id');
    }

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
}
