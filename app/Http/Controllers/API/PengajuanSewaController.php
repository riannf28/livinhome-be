<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ImageBuildProperty;
use App\Models\Transaction\AdditionalFeatures;
use App\Models\Transaction\ListAdditionalFeatures;
use App\Models\Transaction\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
                // ->where('status', null)
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
            $transaction = Transaction::where('id', $id)->first();

            if (empty($transaction)) {
                return ResponseFormatter::error(null, 'Transaksi tidak ditemukan', 404);
            }

            $image_build_property = ImageBuildProperty::where('property_id', $transaction->property_id)->first();

            $transaction->property[0]['image'] = $image_build_property->image_bangunan_depan_url();
            unset($transaction->property[0]->user);

            $transaction['total_price'] = null;

            $transaction->id_card = $transaction->id_card_url();


            if ($transaction->duration == 1) {
                $transaction->rent_end = $this->convertDateToTimestamp(Carbon::parse($transaction->checkin)->addMonth(1));
                $transaction->rent_duration = '1 Bulan';
                $transaction['total_price'] += $transaction->property[0]->harga_sewa_1_bulan;
            }
            if ($transaction->duration == 3) {
                $transaction->rent_end = $this->convertDateToTimestamp(Carbon::parse($transaction->checkin)->addMonth(3));
                $transaction->rent_duration = '3 Bulan';
                $transaction['total_price'] += $transaction->property[0]->harga_sewa_3_bulan;
            }
            if ($transaction->duration == 12) {
                $transaction->rent_end = $this->convertDateToTimestamp(Carbon::parse($transaction->checkin)->addMonth(12));
                $transaction->rent_duration = '1 Tahun';
                $transaction['total_price'] += $transaction->property[0]->harga_sewa_tahun;
            }

            $transaction['total_price'] = $transaction->property[0]->harga_sewa_1_bulan * $transaction->duration;

            $transaction->checkout = $this->convertDateToTimestamp(Carbon::parse($transaction->checkin)->addMonth($transaction->duration));
            $transaction->checkin = $this->convertDateToTimestamp($transaction->checkin);

            if (empty($transaction->proof_of_payment)) {
                $transaction->proof_of_payment = null;
            } else {
                $transaction->proof_of_payment = $transaction->proof_of_payment_url();
            }

            $deadline = Carbon::parse($transaction->created_at)->addHours(env('MAX_PENGAJUAN_HOUR'))->addDays(env('MAX_PENGAJUAN_DAY'));

            if (now()->lt($deadline)) {
                $transaction->deadline = $deadline->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
            } else {
                $transaction->deadline = 'Sudah Terlambat';
            }

            $additional_features = AdditionalFeatures::where('transaction_id', $transaction->id)->get();
            $additional_features_array = [];

            foreach ($additional_features as $item) {
                $list_features = ListAdditionalFeatures::where('id', $item->list_additional_feature_id)->get();

                foreach ($list_features as $feature) {
                    if (!str_contains($feature->icon, 'http')) {
                        $feature->icon = ListAdditionalFeatures::link_location_icon($feature->icon);
                        $transaction['total_price'] += $feature->harga;
                    }
                    $additional_features_array[] = $feature;
                }
            }
            $transaction['additional_features'] = $additional_features_array;
            return ResponseFormatter::success($transaction);
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
            $data = Transaction::where('id', $request->transaction_id)->first();

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
            return ResponseFormatter::success(null, 'Data Not Found');
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
            $transaction = Transaction::where('id', $request->transaction_id)->first();
            if (!empty($transaction)) {

                $transaction->status = false;
                $transaction->save();

                return ResponseFormatter::success($transaction);
            }
            return ResponseFormatter::success(null, 'Data Not Found');
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }
}
