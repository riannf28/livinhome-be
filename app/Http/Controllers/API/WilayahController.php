<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    public function get_province()
    {
        $URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json';
        $data = collect(Http::get($URL)->json());
        return $data;
    }

    public function province($id)
    {
        $URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json';
        $data = collect(Http::get($URL)->json());
        return $data->where('id', $id)->first()['name'];
    }

    public function city($id)
    {
        $URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/regencies/' . $id . '.json';
        $data = collect(Http::get($URL)->json());
        return $data->where('province_id', $id)->first()['name'];
    }

    public function district($id)
    {
        $URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/districts/' . $id . '.json';
        $data = collect(Http::get($URL)->json());
        return $data->where('regency_id', $id)->first()['name'];
    }

    public function village($id)
    {
        $URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/villages/' . $id . '.json';
        $data = collect(Http::get($URL)->json());
        // dd($data);
        return $data->where('district_id', $id)->first()['name'];
    }
}
