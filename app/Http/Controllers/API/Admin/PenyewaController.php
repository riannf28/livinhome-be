<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ListRules;
use App\Models\Property;
use App\Models\RuleProperty;
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
        $transaction = Transaction::with('property', 'user')->where('id', $id)->first();

        if (!$transaction) return ResponseFormatter::error('Data tidak ditemukan', 404);

        $renter = $transaction->user->first();
        $property = $transaction->property->first();

        $availableRules = RuleProperty::where('property_id', $id)->get();

        $result = [
            'id' => $transaction->id,
            'fullname_renter' => $renter->fullname,
            'phone_number' => $transaction->phone_number,
            'email' => $renter->email,
            'date_of_birth' => Carbon::parse($renter->date_of_birth)->translatedFormat('d F Y'),
            'job' => $transaction->job,
            'marriage' => $transaction->marriage,
            'property_name' => $property->nama,
            'checkin' => Carbon::parse($transaction->checkin)->translatedFormat('d F Y'),
            'gender' => $transaction->gender == 'female' ? 'Perempuan' : "Laki-laki",
            'id_card' => asset("uploads/ktp-person/{$transaction->fullname}/{$transaction->id_card}"),
            'property_land_area' => $property->lebar_tanah,
            'property_room_area' => $property->luas_kamar,
            'rules' => $availableRules->map(function ($item) {
                $rule = ListRules::where('id', $item->rule_id)->first();
                return [
                    'name'=> $rule->name,
                ];
            }),
        ];

        return ResponseFormatter::success($result);
    }
}
