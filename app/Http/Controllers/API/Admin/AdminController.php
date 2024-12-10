<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            $total_kontrakan = Property::where('kategori', 'kontrakan')->count();
            $total_kontrakan_user = Transaction::whereHas('property', function ($query) {
                $query->where('kategori', 'kontrakan');
            })
                ->where('payment_date', '!=', null)
                ->count();

            $total_kost = Property::where('kategori', 'kost')->count();
            $total_kost_user = Transaction::whereHas('property', function ($query) {
                $query->where('kategori', 'kost');
            })
                ->where('payment_date', '!=', null)
                ->count();

            $total_apartment = Property::where('kategori', 'apartment')->count();
            $total_apartment_user = Transaction::whereHas('property', function ($query) {
                $query->where('kategori', 'apartment');
            })
                ->where('payment_date', '!=', null)
                ->count();

            // $total_new_transaction = Transaction::where('checkin', '>=', Carbon::now()->subWeek())->count();
            $total_new_transaction = Transaction::where('checkin', '>=', Carbon::now()->subYears(50))
                ->where('payment_date', '!=', null)
                ->count();
            $total_transaction = Transaction::where('payment_date', '!=', null)
                ->count();

            $result = array();

            $result['total_kontrakan'] = $total_kontrakan;
            $result['total_kontrakan_penyewa'] = $total_kontrakan_user;
            $result['total_kost'] = $total_kost;
            $result['total_kost_penyewa'] = $total_kost_user;
            $result['total_apartment'] = $total_apartment;
            $result['total_apartment_penyewa'] = $total_apartment_user;
            $result['total_new_transaction'] = $total_new_transaction;
            $result['total_transaction'] = $total_transaction;

            return ResponseFormatter::success($result);
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }
}
