<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleProperty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'rule_id',
        'created_at',
        'updated_at',
    ];

    public function property()
    {
        return $this->hasMany(Property::class, 'id', 'property_id');
    }

    public function rule()
    {
        return $this->hasMany(ListRules::class, 'id', 'rule_id');
    }
}
