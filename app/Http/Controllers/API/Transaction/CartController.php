<?php

namespace App\Http\Controllers\API\Transaction;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Cart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function add_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $user_id = Auth::user()->id;
            $check_exist_data = Cart::where('user_id', $user_id)->where('property_id', $request->property_id)->first();
            if ($check_exist_data) {
                return ResponseFormatter::error(null, 'Property sudah ada di keranjang', 400);
            }
            $data = new Cart();
            $data->fill($request->all());
            $data->user_id = $user_id;
            $data->save();

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function list_cart()
    {
        $query = Cart::query();

        if (!empty($_REQUEST['sort'] == 'asc')) {
            $query->orderBy('created_at', 'asc');
        } else if (!empty($_REQUEST['sort'] == 'desc')) {
            $query->orderBy('created_at', 'desc');
        }

        if (!empty($_REQUEST['category'])) {
            // $query->where('kategori', $_REQUEST['category']);
            $where = $_REQUEST['category'];
            $query->whereHas('property', function ($query_value) use ($where) {
                $query_value->where('kategori', $where);
            });
        }
        $data = $query->with('user', 'property')->get();
        // $data = !empty($_REQUEST) ? $_REQUEST : 'tidak ada';
        return ResponseFormatter::success($data);
    }
}
