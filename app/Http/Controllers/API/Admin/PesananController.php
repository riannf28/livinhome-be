<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\FacilityProperty;
use App\Models\Transaction\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function index()
    {
        $data = Transaction::with('property')->where('status', '!=', null)
            ->whereHas('property', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get();
        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'renter_name' => $item->fullname,
                'transaction_date' => $item->created_at->translatedFormat('d F Y'),
                'property_name' => $item->property[0]->nama,
                'duration' => $item->duration == 1 ? '1 Bulan' : ($item->duration == 3 ? '3 Bulan' : ($item->duration == 12 ? '1 Tahun' : 'Durasi tidak dikenal')),
                'status' => $item->status == 1 ? "Lunas" : "Belum lunas"
            ];
        }

        return ResponseFormatter::success($result);
    }

    public function detail($id)
    {
        $data = Transaction::with('property')->where('id', $id)->first();
        if ($data) {
            $facilityProperty = FacilityProperty::where('property_id', $id)
                ->with('facility')
                ->get();
            foreach ($facilityProperty as $facility) {
                // Cek apakah relasi facility ada dan tidak kosong
                if ($facility->facility) {
                    foreach ($facility->facility as $fac) {
                        // Tambahkan nama fasilitas ke array
                        $facilityNames[] = $fac->name; // Sesuaikan dengan kolom yang berisi nama fasilitas
                    }
                }
            }
            $facilityString = implode(', ', $facilityNames);
            $result = [
                'id' => $data->id,
                'property_name' => $data->property[0]->nama,
                'type' => $data->property[0]->kategori,
                'checkin' => Carbon::parse($data->checkin)->translatedFormat('d F Y'),
                'checkout' => Carbon::parse($data->checkin)->addMonths($data->duration)->translatedFormat('d F Y'),
                'other_facility' => $facilityString,
                'payment_status' => $data->status == 1 ? "Lunas" : "Belum lunas",
                'fullname_renter' => $data->fullname,
                'phone_number' => $data->phone_number,
                'gender' => $data->gender,
                'id_card' => asset("uploads/ktp-person/{$data->fullname}/{$data->id_card}"),
            ];
            return ResponseFormatter::success($result);
        } else {
            return
                ResponseFormatter::error('Data not found', 404);
        }
    }
}
