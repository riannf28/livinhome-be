<?php

namespace App\Http\Controllers\Transaction;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction\AdditionalFeatures;
use App\Models\Transaction\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index($property_id)
    {
        try {
            $data = Property::query()
                ->select('*')
                ->when(!empty($_REQUEST['durasi-sewa']), function ($query) {
                    $durasiSewa = $_REQUEST['durasi-sewa'];
                    $query->when($durasiSewa == 1, function ($query) {
                        $query->selectRaw('harga_sewa_1_bulan as biaya_sewa');
                    })
                        ->when($durasiSewa == 3, function ($query) {
                            $query->selectRaw('harga_sewa_3_bulan as biaya_sewa');
                        })
                        ->when($durasiSewa != 1 && $durasiSewa != 3, function ($query) {
                            $query->selectRaw('harga_sewa_tahun as biaya_sewa');
                        });
                }, function ($query) {
                    $query->selectRaw('harga_sewa_tahun as biaya_sewa');
                })
                ->where('id', $property_id)
                ->first();

            if ($data) {
                $data->uang_muka = round($data->biaya_sewa * 0.20);
            }


            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function store_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'fullname' => 'required',
            'phone_number' => 'required',
            'gender' => 'required|in:1,0',
            'job' => 'required',
            'duration' => 'required|in:12,1,3',
            'marriage' => 'required|in:single,married,divorced',
            'number_of_renters' => 'required',
            'school_name' => 'required',
            'id_card' => 'required|image|mimes:jpeg,png,jpg',
            'checkin' => 'required',
            'additional_note' => 'nullable',
            'list_additional_feature_id.*' => 'nullable',
        ], [
            'property_id.required' => 'Property ID wajib diisi.',
            'property_id.exists' => 'Property ID tidak ditemukan di database.',
            'fullname.required' => 'Nama lengkap wajib diisi.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'gender.in' => 'Jenis kelamin harus 1 (Pria) atau 0 (Wanita).',
            'job.required' => 'Pekerjaan wajib diisi.',
            'duration.required' => 'Durasi Sewa wajib diisi.',
            'duration.in' => 'Durasi Hanya bisa berisi 1 bulan, 2 bulan, 1 tahun.',
            'marriage.required' => 'Status Kawin Sewa wajib diisi.',
            'marriage.in' => 'Status Kawin hanya bisa diisi kawin/belum kawin.',
            'number_of_renters.required' => 'Jumlah penyewa wajib diisi.',
            'school_name.required' => 'Nama sekolah wajib diisi.',
            'id_card.required' => 'KTP wajib diunggah.',
            'checkin.required' => 'Tanggal Masuk harus diisi.',
            'id_card.image' => 'KTP harus berupa gambar.',
            'id_card.mimes' => 'KTP harus dalam format JPEG, PNG, atau JPG.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            if ($request->hasFile('id_card')) {
                $image = $request->file('id_card');
                $image_name = time() . '-' . $request->fullname . '.' . $image->getClientOriginalExtension();
                Storage::putFileAs("public/uploads/transaction/ktp/{$request->fullname}", $image, $image_name);
            }
            $transaction = new Transaction();
            $transaction->fill($request->all());
            $transaction->id_card = $image_name;
            $transaction->checkin = ResponseFormatter::timestampToDate($request->checkin);
            $transaction->save();

            $total_additionl_features = count($request->list_additional_feature_id);
            for ($i = 0; $i < $total_additionl_features; $i++) {
                $additionl_features = new AdditionalFeatures();
                $additionl_features->transaction_id = $transaction->id;
                $additionl_features->list_additional_feature_id = $request->list_additional_feature_id[$i];
                $additionl_features->save();
            }


            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }

    public function payment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'transaction_id' => 'required|exists:transactions,id',
                'bank' => 'required|in:bca,bni,bri,bsi,mandiri',
            ],
            [
                'transaction_id.required' => 'Property harus dipilih.',
                'transaction_id.exists' => 'Property tidak tersedia.',
                'bank.required' => 'Bank harus diisi.',
                'bank.in' => 'Bank harus salah satu dari: bca, bni, bri, bsi, mandiri.',
            ]
        );

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $property = Transaction::where('id', $request->transaction_id)->first();
            if ($property->status == false) {
                return ResponseFormatter::error(null, 'Pengajuan ditolak');
            }

            if ($property->status == null) {
                return ResponseFormatter::error(null, 'Pengajuan belum di proses');
            }

            $property->bank = $request->bank;
            $property->booking_code = Transaction::generate_kode();
            $property->payment_date = Carbon::now();
            $property->save();

            $result_checkin = ResponseFormatter::dateToTimestamp($property->checkin);

            return ResponseFormatter::success($result_checkin);
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }
}
