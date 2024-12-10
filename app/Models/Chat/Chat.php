<?php

namespace App\Models\Chat;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'sender_id',
        'receiver_id',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function chat_detail()
    {
        return $this->hasMany(ChatDetails::class, 'chat_id');
    }

    public function sender()
    {
        return $this->hasMany(User::class, 'id', 'sender_id');
    }

    public function receiver()
    {
        return $this->hasMany(User::class, 'id', 'receiver_id');
    }
}
