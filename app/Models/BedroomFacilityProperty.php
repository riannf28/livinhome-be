<?php

namespace App\Models;

use App\Utils\StoragePath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        return $this->belongsTo(Property::class, 'id', 'property_id');
    }

    public function image_url() {
        $basePath = StoragePath::propertyPath($this->property_id);
        return isset($this->image) ? Storage::url("$basePath/kamar-tidur/$this->image") : null;
    }
}
