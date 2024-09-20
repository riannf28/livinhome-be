<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListFacility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'updated_at',
    ];

    public function facility_property()
    {
        return $this->belongsTo(FacilityProperty::class);
    }
}
