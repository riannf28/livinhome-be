<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthAdminController extends Controller
{
    use HasApiTokens;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ], [
            'email.required' => 'Alamat email harus diisi.',
            'email.exists' => 'Alamat email tidak terdaftar.',
            'password.required' => 'Password harus diisi.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if ($user->roles != 'admin') {
                return ResponseFormatter::error(null, 'Access denied. Your role does not have the necessary permissions to perform this action', 401);
            }

            if (!Hash::check($request->password,  $user->password, [])) {
                return ResponseFormatter::error(null, 'Wrong password', 401);
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }

        return ResponseFormatter::success($user->roles, 'Login Successfully', $tokenResult);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'email' => 'required|email:rfc,dns',
            'date_of_birth' => 'required',
            'phone_number' => 'required',
            'password' => 'required',
            'gender' => 'required|in:1,0',
        ], [
            'fullname.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Alamat email harus diisi.',
            'email.unique' => 'Alamat email sudah terdaftar. Silakan gunakan alamat email lain.',
            'email.email' => 'Pastikan alamat email sudah benar dan dapat dihubungi.',
            'date_of_birth.required' => 'Tanggal lahir harus diisi.',
            'date_of_birth.date' => 'Tanggal lahir harus dalam format yang valid.',
            'phone_number.required' => 'Nomor telepon harus diisi.',
            'password.required' => 'Password harus diisi.',
            'gender.in' => 'Gender hanya bisa diisi male / female'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);

        $check_phone_number_exist = User::where('phone_number', $phone_number)->first();
        if (!empty($check_phone_number_exist)) {
            return ResponseFormatter::error(null, 'Nomor Telp sudah terdaftar', 400);
        }

        $check_email_exist = User::where('email', $request->email)->first();
        if (!empty($check_email_exist)) {
            return ResponseFormatter::error(null, 'Email Sudah Terdaftar', 400);
        }

        try {
            $data = new User();
            $data->fill($request->all());
            $data->phone_number = $phone_number;
            $data->date_of_birth = ResponseFormatter::timestampToDate($request->date_of_birth);
            $data->roles = 'admin';
            $data->password = Hash::make($request->password);
            $data->save();

            $tokenResult = $data->createToken('authToken')->plainTextToken;

            // return ResponseFormatter::success($tokenResult, 'User successfully registered');
            return ResponseFormatter::success(null, 'User successfully registered', $tokenResult);
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success('Token Revoked', 'success');
    }
}
