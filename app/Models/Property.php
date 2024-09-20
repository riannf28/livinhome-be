<?php

namespace App\Models;

use App\Models\Chat\Chat;
use App\Models\Transaction\Cart;
use App\Models\Transaction\Survey;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nama',
        'deskripsi',
        'tanggal_dibuat',
        'tanggal_mulai_sewa',
        'sewa_untuk',
        'kategori',
        'latitude',
        'longitude',
        'provinsi',
        'kota',
        'kecamatan',
        'alamat',
        'catatan_alamat',
        'fasilitas',
        'lebar_tanah',
        'daya_listrik',
        'sumber_air',
        'kapasitas_motor',
        'kapasitas_mobil',
        'total_kamar',
        'total_lemari',
        'minimum_sewa',
        'meja',
        'kasur',
        'harga_sewa_tahun',
        'harga_sewa_1_bulan',
        'harga_sewa_3_bulan',
        'bank',
        'rekening',
        'status',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }

    public function rule_property()
    {
        return $this->belongsTo(RuleProperty::class);
    }

    public function bedroom_facility()
    {
        return $this->belongsTo(BedroomFacilityProperty::class);
    }

    public function image_property()
    {
        return $this->belongsTo(ImageBuildProperty::class);
    }

    public function image_bathroom()
    {
        return $this->belongsTo(ImageBathroomProperty::class);
    }

    public function facility_property()
    {
        return $this->belongsTo(FacilityProperty::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function rating_property()
    {
        return $this->belongsTo(RatingProperty::class);
    }
}
