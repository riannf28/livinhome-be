<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListAdditionalFeatures extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'icon',
    ];

    public function additional_features()
    {
        return $this->belongsTo(AdditionalFeatures::class);
    }

    public static function link_location_icon($icon)
    {
        return asset("uploads/image/{$icon}");
    }
}
