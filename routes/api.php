<?php

use App\Http\Controllers\API\Auth\AuthAdminController;
use App\Http\Controllers\API\Auth\AuthOwnerController;
use App\Http\Controllers\API\Auth\AuthRenterController;
use App\Http\Controllers\API\Auth\OTP\OTPController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\HomepageController;
use App\Http\Controllers\API\PengajuanSewaController;
use App\Http\Controllers\API\Profile\ProfileController;
use App\Http\Controllers\API\Profile\ProfileOwnerController;
use App\Http\Controllers\API\PropertyController;
use App\Http\Controllers\API\SurveyController as APISurveyController;
use App\Http\Controllers\API\Transaction\CartController;
use App\Http\Controllers\API\Transaction\SurveyController;
use App\Http\Controllers\API\WilayahController;
use App\Http\Controllers\Transaction\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::group([
        'prefix' => 'auth',
        'middleware' => ['auth', 'role:owner']
    ], function () {
        Route::post('upload-ktp/owner', [AuthOwnerController::class, 'upload_ktp']);
        Route::post('upload-ktp-with-person/owner', [AuthOwnerController::class, 'upload_ktp_with_person']);
    });

    Route::group([
        'prefix' => 'profile/renter',
        'middleware' => ['auth', 'role:renter']
    ], function () {
        Route::get('', [ProfileController::class, 'index']);
        Route::post('update', [ProfileController::class, 'update']);
    });

    Route::group([
        'prefix' => 'profile/owner',
        'middleware' => ['auth', 'role:owner']
    ], function () {
        Route::get('', [ProfileOwnerController::class, 'index']);
        Route::post('update', [ProfileOwnerController::class, 'update']);
    });

    Route::group([
        'prefix' => 'property',
        'middleware' => ['auth', 'role:renter']
    ], function () {
        Route::get('detail-property/{id}', [PropertyController::class, 'detail_property']);
    });

    Route::group([
        'prefix' => 'property',
        'middleware' => ['auth', 'role:owner']
    ], function () {
        Route::post('register', [PropertyController::class, 'register_property']);
        Route::get('get-rules', [PropertyController::class, 'get_rules']);
        Route::get('get-facilities', [PropertyController::class, 'get_facilites']);

        // MANAJEMEN PROPERTY
        Route::get('list-property', [PropertyController::class, 'list_property']);
        Route::get('get-data-property/{id}', [PropertyController::class, 'get_property']);
        Route::post('update-property', [PropertyController::class, 'update']);
    });

    Route::group([
        'prefix' => 'pengajuan',
        'middleware' => ['auth', 'role:owner']
    ], function () {
        Route::get('list', [PengajuanSewaController::class, 'list_pengajuan']);
        Route::get('detail/{id}', [PengajuanSewaController::class, 'pengajuan_detail']);
        Route::post('accept', [PengajuanSewaController::class, 'accept_pengajuan']);
        Route::post('decline', [PengajuanSewaController::class, 'decline_pengajuan']);
    });

    Route::group([
        'prefix' => 'cart',
        'middleware' => ['auth', 'role:renter']
    ], function () {
        Route::get('', [CartController::class, 'list_cart']);
        Route::post('add-cart', [CartController::class, 'add_cart']);
    });

    // Renter
    Route::group([
        'prefix' => 'survey',
        'middleware' => ['auth', 'role:renter']
    ], function () {
        Route::post('submit-survey', [SurveyController::class, 'submit_survey']);
        Route::post('edit-submit-survey', [SurveyController::class, 'edit_submit_survey']);
        Route::post('cancel-submit', [SurveyController::class, 'cancel_survey']);
    });

    // Owner
    Route::group([
        'prefix' => 'survey',
        'middleware' => ['auth', 'role:owner']
    ], function () {
        Route::get('calon-penyewa', [APISurveyController::class, 'calon_penyewa']);
        Route::get('tolak-survey', [APISurveyController::class, 'tolak_survey']);
        Route::post('confirm-survey', [APISurveyController::class, 'confirm_survey']);
        Route::post('reject-survey', [APISurveyController::class, 'reject_survey']);
    });

    Route::group([
        'prefix' => 'transaction',
        'middleware' => ['auth', 'role:renter']
    ], function () {
        Route::get('get-data-property/{property_id}', [TransactionController::class, 'index']);
        Route::post('store-transaction-data', [TransactionController::class, 'store_transaction']);
        Route::post('payment', [TransactionController::class, 'payment']);
    });

    Route::group([
        'prefix' => 'homepage',
        'middleware' => ['auth', 'role:owner']
    ], function () {
        Route::get('beranda', [HomepageController::class, 'beranda']);
        Route::get('list-pemilik', [HomepageController::class, 'list_pemilik']);
    });
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login/owner', [AuthOwnerController::class, 'login']);
    Route::post('login/renter', [AuthRenterController::class, 'login']);
    Route::post('login/admin', [AuthAdminController::class, 'login']);

    Route::post('register/owner', [AuthOwnerController::class, 'register']);
    Route::post('register/renter', [AuthRenterController::class, 'register']);
});

Route::post('sent-whatsapp', [OTPController::class, 'sent_otp_whatsapp']);
Route::post('varify-otp-whatsapp', [OTPController::class, 'verify_otp_whatsapp']);

Route::post('sent-email', [OTPController::class, 'sent_otp_email']);
Route::post('verify-otp-email', [OTPController::class, 'verify_otp_email']);

Route::post('reset-password', [OTPController::class, 'reset_password']);

Route::group([
    'prefix' => 'get-data'
], function () {
    Route::get('get-province', [WilayahController::class, 'get_province']);
    Route::get('province/{id}', [WilayahController::class, 'province']);
    Route::get('city/{id}', [WilayahController::class, 'city']);
    Route::get('district/{id}', [WilayahController::class, 'district']);
    Route::get('village/{id}', [WilayahController::class, 'village']);
});
