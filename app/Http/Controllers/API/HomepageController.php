<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomepageController extends Controller
{
    public function beranda()
    {
        try {
            $user = Auth::user();

            $total_property = Property::where('user_id', $user->id)->count();
            $total_transaksi = Transaction::whereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->where('status', 1)
                ->count();

            $total_rental = Property::where('user_id', $user->id)
                ->where('kategori', 'rental')
                ->count();
            $total_penyewa_rental = Transaction::whereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('kategori', 'rental');
            })
                ->where('status', 1)
                ->count();

            $total_kost = Property::where('user_id', $user->id)
                ->where('kategori', 'kost')
                ->count();
            $total_penyewa_kost = Transaction::whereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('kategori', 'kost');
            })
                ->where('status', 1)
                ->count();

            $total_apartement = Property::where('user_id', $user->id)
                ->where('kategori', 'apartement')
                ->count();

            $total_penyewa_apartement = Transaction::whereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('kategori', 'apartement');
            })
                ->where('status', 1)
                ->count();

            $result = array();
            $result['name'] = $user->fullname;
            $result['total_property'] = $total_property;
            $result['total_transaksi'] = $total_transaksi;
            $result['total_rental'] = $total_rental;
            $result['total_penyewa_rental'] = $total_penyewa_rental;
            $result['total_kost'] = $total_kost;
            $result['total_penyewa_kost'] = $total_penyewa_kost;
            $result['total_apartement'] = $total_apartement;
            $result['total_penyewa_apartement'] = $total_penyewa_apartement;

            return ResponseFormatter::success($result);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function list_pemilik()
    {
        try {
            $user = Auth::user();

            $total_property = Property::where('user_id', $user->id)->count();
            $total_transaksi = Transaction::whereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->where('status', 1)
                ->count();

            $result = array();
            $result['name'] = $user->fullname;
            $result['total_property'] = $total_property;
            $result['total_transaksi'] = $total_transaksi;

            return ResponseFormatter::success($result);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }
}
