<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BedroomFacilityProperty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'image',
        'updated_at',
    ];

    public function property()
    {
        return $this->hasMany(Property::class, 'id', 'property_id');
    }
}
