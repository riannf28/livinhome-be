<?php

namespace App\Models;

use App\Utils\StoragePath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ImageBuildProperty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'bangunan_depan',
        'depan',
        'dalam',
        'updated_at',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'id', 'property_id');
    }

    public function image_bangunan_depan_url() {
        $basePath = StoragePath::propertyPath($this->property_id);
        return isset($this->bangunan_depan) ? Storage::url("$basePath/$this->bangunan_depan") : null;
    }

    public function image_depan_url() {
        $basePath = StoragePath::propertyPath($this->property_id);
        return isset($this->depan) ? Storage::url("$basePath/$this->depan") : null;
    }

    public function image_dalam_url() {
        $basePath = StoragePath::propertyPath($this->property_id);
        return isset($this->dalam) ? Storage::url("$basePath/$this->dalam") : null;
    }
}
