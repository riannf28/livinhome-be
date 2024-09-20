<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReasonCancel extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason'
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
