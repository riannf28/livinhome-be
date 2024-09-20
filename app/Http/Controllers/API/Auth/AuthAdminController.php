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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success('Token Revoked', 'success');
    }
}
