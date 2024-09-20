<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction\AdditionalFeatures;
use App\Models\Transaction\ListAdditionalFeatures;
use App\Models\Transaction\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PengajuanSewaController extends Controller
{
    public function list_pengajuan()
    {
        try {
            $user = Auth::user();
            $data = Transaction::whereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->where('status', null)
                ->with('property')
                ->get();
            $data->each(function ($item) {
                $item->tanggal_masuk = ResponseFormatter::dateToTimestamp($item->created_at);
                $deadline = Carbon::parse($item->created_at)->addHours(env('MAX_PENGAJUAN_HOUR'))->addDays(env('MAX_PENGAJUAN_DAY'));

                if (now()->lt($deadline)) {
                    $item->deadline = $deadline->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
                } else {
                    $item->deadline = 'Sudah Terlambat';
                }
            });

            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function pengajuan_detail($id)
    {
        try {
            $user = Auth::user();
            $data = Transaction::where('id', $id)
                ->whereHas('property', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('status', null)
                ->first();

            if (!empty($data)) {


                $data['total_price'] = null;

                $data->id_card = asset("uploads/transaction/ktp/{$data->fullname}/{$data->id_card}");
                if ($data->duration == 1) {
                    $data->rent_end = $this->convertDateToTimestamp(Carbon::parse($data->checkin)->addMonth(1));
                    $data->rent_duration = '1 Bulan';
                    $data['total_price'] += $data->property[0]->harga_sewa_1_bulan;
                }
                if ($data->duration == 3) {
                    $data->rent_end = $this->convertDateToTimestamp(Carbon::parse($data->checkin)->addMonth(3));
                    $data->rent_duration = '3 Bulan';
                    $data['total_price'] += $data->property[0]->harga_sewa_3_bulan;
                }
                if ($data->duration == 12) {
                    $data->rent_end = $this->convertDateToTimestamp(Carbon::parse($data->checkin)->addMonth(12));
                    $data->rent_duration = '1 Tahun';
                    $data['total_price'] += $data->property[0]->harga_sewa_tahun;
                }
                $data->checkin = $this->convertDateToTimestamp($data->checkin);

                $deadline = Carbon::parse($data->created_at)->addHours(env('MAX_PENGAJUAN_HOUR'))->addDays(env('MAX_PENGAJUAN_DAY'));

                if (now()->lt($deadline)) {
                    $data->deadline = $deadline->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
                } else {
                    $data->deadline = 'Sudah Terlambat';
                }

                $additional_features = AdditionalFeatures::where('transaction_id', $data->id)->get();
                $additional_features_array = [];

                foreach ($additional_features as $item) {
                    $list_features = ListAdditionalFeatures::where('id', $item->list_additional_feature_id)->get();

                    foreach ($list_features as $feature) {
                        if (!str_contains($feature->icon, 'http')) {
                            $feature->icon = ListAdditionalFeatures::link_location_icon($feature->icon);
                            $data['total_price'] += $feature->harga;
                        }
                        $additional_features_array[] = $feature;
                    }
                }

                $data['additional_features'] = $additional_features_array;
                return ResponseFormatter::success($data);
            }
            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function convertDateToTimestamp($date)
    {
        return ResponseFormatter::dateToTimestamp($date);
    }

    public function accept_pengajuan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
        ], [
            'id.required' => 'ID transaksi wajib diisi.',
            'id.exists' => 'ID transaksi tidak ditemukan di database.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $data = Transaction::where('id', $request->transaction_id)
                ->where('status', null)
                ->first();

            if (!empty($data)) {

                $data->status = true;
                $data->save();

                $result = $data;
                $result['total_price'] = null;
                if ($result->duration == 1) {
                    $result->rent_end = $this->convertDateToTimestamp(Carbon::parse($result->checkin)->addMonth(1));
                    $result->rent_duration = '1 Bulan';
                    $result['total_price'] += $result->property[0]->harga_sewa_1_bulan;
                }
                if ($result->duration == 3) {
                    $result->rent_end = $this->convertDateToTimestamp(Carbon::parse($result->checkin)->addMonth(3));
                    $result->rent_duration = '3 Bulan';
                    $result['total_price'] += $result->property[0]->harga_sewa_3_bulan;
                }
                if ($result->duration == 12) {
                    $result->rent_end = $this->convertDateToTimestamp(Carbon::parse($result->checkin)->addMonth(12));
                    $result->rent_duration = '1 Tahun';
                    $result['total_price'] += $result->property[0]->harga_sewa_tahun;
                }
                $result->checkin = $this->convertDateToTimestamp($data->checkin);

                $additional_features = AdditionalFeatures::where('transaction_id', $data->id)->get();
                $additional_features_array = [];

                foreach ($additional_features as $item) {
                    $list_features = ListAdditionalFeatures::where('id', $item->list_additional_feature_id)->get();

                    foreach ($list_features as $feature) {
                        if (!str_contains($feature->icon, 'http')) {
                            $result['total_price'] += $feature->harga;
                        }
                        $additional_features_array[] = $feature;
                    }
                }

                unset($result->property);

                return ResponseFormatter::success($result);
            }
            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function decline_pengajuan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
        ], [
            'id.required' => 'ID transaksi wajib diisi.',
            'id.exists' => 'ID transaksi tidak ditemukan di database.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $data = Transaction::where('id', $request->transaction_id)->first();
            $data->status = false;
            $data->save();

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }
}
