<?php

namespace App\Models\Chat;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
    ];

    public function property()
    {
        return $this->hasMany(Property::class, 'id', 'property_id');
    }

    public function chat_detail()
    {
        return $this->belongsTo(ChatDetails::class);
    }
}
