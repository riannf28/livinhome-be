<?php

namespace App\Models\Transaction;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_id',
        'jam_mulai',
        'jam_selesai',
        'tanggal',
        'status',
        'is_cancel',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }

    public function property()
    {
        return $this->hasMany(Property::class, 'id', 'property_id');
    }
}
