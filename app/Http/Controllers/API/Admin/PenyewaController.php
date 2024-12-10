<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class PenyewaController extends Controller
{
    public function index($type)
    {
        $data = Transaction::whereHas('property', function ($query) use ($type) {
            $query->where('kategori', $type);
        })->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'renter_name' => $item->fullname,
                'property_name' => $item->property[0]->nama,
                'phone_number' => $item->phone_number,
                'checkin' => Carbon::parse($item->checkin)->translatedFormat('d F Y'),
            ];
        }
        return ResponseFormatter::success($result);
    }

    public function detail($id)
    {
        $data = Transaction::with('property')->where('id', $id)->first();
        if ($data) {
            $result = [
                'id' => $data->id,
                'fullname_renter' => $data->fullname,
                'phone_number' => $data->phone_number,
                'email' => $data->email,
                'date_of_birth' => Carbon::parse($data->date_of_birth)->translatedFormat('d F Y'),
                'job' => $data->job,
                'marriage' => $data->marriage,

                'property_name' => $data->property[0]->nama,
                'checkin' => Carbon::parse($data->checkin)->translatedFormat('d F Y'),
                'gender' => $data->gender == 'female' ? 'Perempuan' : "Laki-laki",
                'id_card' => asset("uploads/ktp-person/{$data->fullname}/{$data->id_card}"),
            ];
            return ResponseFormatter::success($result);
        } else {
            return ResponseFormatter::error('Data tidak ditemukan', 404);
        }
    }
}
