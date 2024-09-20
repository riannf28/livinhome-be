<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalFeatures extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'list_additional_feature_id',
    ];

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'id', 'transaction_id');
    }

    public function list_features()
    {
        return $this->hasMany(ListAdditionalFeatures::class, 'id', 'list_additional_feature_id');
    }
}
