<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthOwnerController extends Controller
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

            if ($user->roles != 'owner') {
                return ResponseFormatter::error(null, 'Access denied. Your role does not have the necessary permissions to perform this action', 401);
            }

            if (!Hash::check($request->password,  $user->password, [])) {
                return ResponseFormatter::error(null, 'Wrong password', 401);
            }

            // $credentials = request(['email', 'password']);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return ResponseFormatter::error(null, 'Authentication Failed', 401);
            }

            // if (!Auth::attempt($credentials)) {
            //     return ResponseFormatter::error(null, 'Authentication Failed', 401);
            // }


            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success($user->roles, 'Login Successfully', $tokenResult);
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
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

        // return response()->json(ResponseFormatter::timestampToDate($request->date_of_birth));

        try {
            $data = new User();
            $data->fill($request->all());
            $data->phone_number = $phone_number;
            $data->date_of_birth = ResponseFormatter::timestampToDate($request->date_of_birth);
            $data->roles = 'owner';
            $data->password = Hash::make($request->password);
            $data->save();

            $tokenResult = $data->createToken('authToken')->plainTextToken;

            // return ResponseFormatter::success($tokenResult, 'User successfully registered');
            return ResponseFormatter::success(null, 'User successfully registered', $tokenResult);
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }

    public function upload_ktp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg',
        ], [
            'image.required' => 'Gambar harus diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus memiliki format: jpeg, png, atau jpg.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $image = $request->file('image');
        $image_name = time() . '-' . $request->fullname . '.' . $image->getClientOriginalExtension();
        try {
            $user = Auth::user();
            Storage::putFileAs("public/uploads/ktp/{$user->fullname}", $image, $image_name);
            $user->id_card = $image_name;
            $user->save();

            $link_image = asset("uploads/ktp/{$user->fullname}/{$image_name}");

            return ResponseFormatter::success($link_image, 'Upload Successfully');
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }

    public function upload_ktp_with_person(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg',
        ], [
            'image.required' => 'Gambar harus diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus memiliki format: jpeg, png, atau jpg.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $image = $request->file('image');
        $image_name = time() . '-' . $request->fullname . '.' . $image->getClientOriginalExtension();
        try {
            $user = Auth::user();
            Storage::putFileAs("public/uploads/ktp-person/{$user->fullname}", $image, $image_name);
            $user->id_card_with_person = $image_name;
            $user->save();

            return ResponseFormatter::success(null, 'Upload Successfully');
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }
}
