<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityProperty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'facility_id',
        'updated_at',
    ];

    public function property()
    {
        return $this->hasMany(Property::class, 'id', 'property_id');
    }

    public function facility()
    {
        return $this->hasMany(ListFacility::class, 'id', 'facility_id');
    }
}
