<?php

namespace App\Http\Controllers\API\Auth\OTP;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Mail\SentOTP;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class OTPController extends Controller
{
    protected $maxAttempts;
    protected $blockTime;
    protected $expiresOTP;

    public function __construct()
    {
        $this->maxAttempts = env('MAX_ATTEMPT_OTP', 3);
        $this->blockTime = env('BLOCK_TIME', 300);
        $this->expiresOTP = env('EXPIRES_OTP', 300);
    }

    public function sent_otp_whatsapp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
        ], [
            'phone_number.required' => 'Nomor Telp harus diisi',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);

        $check_phone_number_exist = User::where('phone_number', $phone_number)->first();
        if (empty($check_phone_number_exist)) {
            return ResponseFormatter::error(null, 'Nomor Telp tidak tersedia', 400);
        }

        // composer require twilio/sdk
        $twillioID = env('TWILIO_SID');
        $twillioToken = env('TWILIO_TOKEN');
        $twillioWhatsappNumber = env('TWILIO_WHATSAPP_FROM');
        $recieptNumber = "whatsapp:+{$phone_number}";
        // $message = 'hallo testing';

        $otp = rand(1000, 9999);

        Cache::put('otp_' . $phone_number, $otp, $this->expiresOTP);

        Cache::forget('otp_attempts_' . $phone_number);

        $message = "Your OTP is: " . $otp;
        // dd($recieptNumber, $twillioWhatsappNumber);

        try {
            $twillio = new Client($twillioID, $twillioToken);

            $twillio->messages->create(
                $recieptNumber,
                // 'whatsapp:+6281231526295',
                // 'whatsapp:+6281216913886',
                [
                    'from' => $twillioWhatsappNumber,
                    'body' => $message
                ]
            );

            return ResponseFormatter::success(null, 'Success sent whatsapp');
        } catch (Exception $error) {
            dd($error);
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function verify_otp_whatsapp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|exists:users,phone_number',
            'OTP' => 'required|numeric',
        ], [
            'phone_number.required' => 'Nomor Telp harus diisi',
            'OTP.required' => 'OTP Harus diisi',
            'OTP.numeric' => 'OTP Harus berisi angka',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);

        $cachedOtp = Cache::get('otp_' . $phone_number);

        if ($cachedOtp && $cachedOtp == $request->OTP) {
            Cache::forget('otp_' . $phone_number);
            Cache::forget('otp_attempts_' . $phone_number);
            return ResponseFormatter::success(null, 'OTP verified successfully');
        } else {
            $attempts = Cache::increment('otp_attempts_' . $phone_number);

            if ($attempts >= $this->maxAttempts) {
                Cache::put('otp_block_' . $phone_number, true, $this->blockTime);

                return ResponseFormatter::error(null, 'Too many failed attempts. Please try again after 5 minutes.', 429);
            }

            return ResponseFormatter::error(null, 'Invalid OTP.', 400);
        }
    }

    public function sent_otp_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:rfc,dns|exists:users,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Pastikan alamat email sudah benar dan dapat dihubungi.',
            'email.exists' => 'Email tidak tersedia',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $otp = rand(1000, 9999);

        Cache::put('otp_' . $request->email, $otp, $this->expiresOTP);

        Cache::forget('otp_attempts_' . $request->email);

        $user_name = User::where('email', $request->email)->pluck('fullname')[0];

        $date = Carbon::now();
        $formattedDate = $date->formatLocalized('%A, %d %B %Y');
        $request["date"] = $formattedDate;
        $request["name"] = $user_name;
        $message = "Your OTP is: " . $otp;

        $request['message'] = $message;

        try {
            Mail::send(new SentOTP($request));

            return ResponseFormatter::success(null, 'OTP successfully sent');
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function verify_otp_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:rfc,dns|exists:users,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Pastikan alamat email sudah benar dan dapat dihubungi.',
            'email.exists' => 'Email tidak tersedia',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $cachedOtp = Cache::get('otp_' . $request->email);

        if ($cachedOtp && $cachedOtp == $request->OTP) {
            Cache::forget('otp_' . $request->email);
            Cache::forget('otp_attempts_' . $request->email);
            Cache::forget('otp_block_' . $request->email);
            return ResponseFormatter::success(null, 'OTP verified successfully');
        } else {
            $attempts = Cache::increment('otp_attempts_' . $request->email);

            if ($attempts >= $this->maxAttempts) {
                Cache::put('otp_block_' . $request->email, true, $this->blockTime);

                return ResponseFormatter::error(null, 'Too many failed attempts. Please try again after 5 minutes.', 429);
            }

            return ResponseFormatter::error(null, 'Invalid OTP.', 400);
        }

        return ResponseFormatter::error(null, 'Invalid OTP.', 400);
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable',
            'email' => 'nullable|email:rfc,dns',
            'password' => 'required|min:6|confirmed',
        ], [
            'phone_number.exists' => 'Nomor telepon yang Anda masukkan tidak tersedia.',
            'email.email' => 'Pastikan alamat email valid dan dapat dihubungi.',
            'email.exists' => 'Email yang Anda masukkan tidak tersedia.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus terdiri dari 6 karakter.',
            'password.confirmed' => 'Password dan konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        if ($request->has('phone_number')) {

            $phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);

            if (Cache::has('otp_block_' . $phone_number)) {
                return ResponseFormatter::error(null, 'Too many failed attempts. Please try again after 5 minutes.', 429);
            }
            $user = User::where('phone_number', $phone_number)->first();
            Cache::forget('otp_block_' . $phone_number);
        }

        if ($request->has('email')) {
            if (Cache::has('otp_block_' . $request->email)) {
                return ResponseFormatter::error(null, 'Too many failed attempts. Please try again after 5 minutes.', 429);
            }

            $user = User::where('email', $request->email)->first();
            Cache::forget('otp_block_' . $request->email);
        }

        $user->password = Hash::make($request->password);
        $user->save();


        return ResponseFormatter::success(null, 'Password successfully changed');
    }
}
