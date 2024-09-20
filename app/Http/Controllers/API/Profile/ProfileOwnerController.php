<?php

namespace App\Http\Controllers\API\Profile;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Property;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileOwnerController extends Controller
{
    public function index()
    {
        try {
            $data = Auth::user();
            $data->id_card = asset("uploads/ktp/{$data->fullname}/" . $data->id_card);

            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'gender' => 'required|in:1,0',
            'email' => 'required|email:rfc,dns',
            'phone_number' => 'required',
            'bank' => 'required|in:bca,bni,bri,bsi,mandiri',
            'rekening' => 'required',
        ], [
            'fullname.required' => 'Nama lengkap harus diisi.',
            'fullname.exists' => 'Nama lengkap yang dipilih tidak ada dalam catatan kami.',
            'gender.required' => 'Jenis kelamin harus diisi.',
            'gender.in' => 'Jenis kelamin harus salah satu dari: laki-laki atau perempuan.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Silakan masukkan alamat email yang valid.',
            'email.email:rfc,dns' => 'Alamat email harus valid sesuai standar RFC dan DNS.',
            'phone_number.required' => 'Nomor telepon harus diisi.',
            'bank.required' => 'Pemilihan bank harus diisi.',
            'bank.in' => 'Bank harus salah satu dari: bca, bni, bri, bsi, mandiri.',
            'rekening.required' => 'Nomor rekening bank harus diisi.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {

            $user = Auth::user();
            $user->fullname = $request->fullname;
            $user->gender = $request->gender;
            $user->email = $request->email;
            $user->phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);
            $user->save();

            Property::where('user_id', $user->id)->update([
                'bank' => $request->bank,
                'rekening' => $request->rekening
            ]);

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }
}
