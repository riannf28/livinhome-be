<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatDetails;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function list_chat()
    {
        $data = Chat::whereHas('chat_detail', function ($query) {
            $query->where('sender_id',  auth()->user()->id);
        })
            ->with(['receiver' => function ($query) {
                $query->select('id', 'photo_profile', 'fullname');
            }])
            ->get();

        $data->map(function ($chat) {
            if ($chat->receiver) {
                $chat->receiver[0]->photo_profile = asset("uploads/photo-profile/{$chat->receiver[0]->fullname}/" . $chat->receiver[0]->photo_profile);
            }
            return $chat;
        });

        if (isset($data[0])) {
            return ResponseFormatter::success($data);
        }

        return ResponseFormatter::success(null, 'Data Not Found');
    }

    public function store_chat_owner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
        ], [
            'property_id.required' => 'Property harus dipilih.',
            'property_id.exists' => 'Property tidak tersedia.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $property = Property::where('id', $request->property_id)->first();

        if (empty($property)) {
            return ResponseFormatter::success(null, 'Data Not Found');
        }

        $receiver_id = $property->user_id;

        $check_has_chat = Chat::where('property_id', $request->property_id)
            ->where('sender_id', auth()->user()->id)
            ->where('receiver_id', $receiver_id)
            ->first();

        if (!empty($check_has_chat)) {
            return ResponseFormatter::success([
                'chat_id' => $check_has_chat->id
            ]);
        }

        $data = Chat::create([
            'property_id' => $request->property_id,
            'sender_id' => auth()->user()->id,
            'receiver_id' => $receiver_id,
        ]);

        return ResponseFormatter::success($data);
    }

    public function store_chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required'
        ], [
            'chat_id.required' => 'Chat harus dipilih.',
            'chat_id.exists' => 'Chat tidak tersedia.',
            'message.required' => 'Pesan harus diisi.'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $chat = Chat::where('id', $request->chat_id)
            ->where(function ($query) {
                $query->where('sender_id', auth()->user()->id)
                    ->orWhere('receiver_id', auth()->user()->id);
            })
            ->first();

        if (!$chat) {
            return ResponseFormatter::error(null, 'Anda tidak berhak mengirim pesan di chat ini.', 403);
        }

        // Buat pesan baru di ChatDetails
        $message = ChatDetails::create([
            'chat_id' => $request->chat_id,
            'sender_id' => auth()->user()->id,
            'message' => $request->message,
        ]);

        return ResponseFormatter::success($message, 'Pesan berhasil dikirim.');
    }

    public function detail_chat($chat_id)
    {
        $chat = Chat::where('id', $chat_id)
            ->where(function ($query) {
                $query->where('sender_id', auth()->user()->id)
                    ->orWhere('receiver_id', auth()->user()->id);
            })
            ->first();

        if (empty($chat)) {
            return ResponseFormatter::success(null, 'No chat messages found.');
        }

        $chat_details = ChatDetails::where('chat_id', $chat_id)
            ->orderBy('created_at', 'asc')
            ->get();

        ChatDetails::where('chat_id', $chat_id)
            ->where('sender_id', '!=', auth()->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $property = Property::find($chat->property_id);

        $data_property = [];

        $data_property['name'] = $property->nama;
        $data_property['total_kamar'] = $property->total_kamar;
        $data_property['kamar_mandi'] = $property->kamar_mandi;
        $data_property['luas_kamar'] = $property->luas_kamar;
        $data_property['harga_sewa_1_bulan'] = $property->harga_sewa_1_bulan;
        $data_property['harga_sewa_3_bulan'] = $property->harga_sewa_3_bulan;
        $data_property['harga_sewa_12_bulan'] = $property->harga_sewa_tahun;
        $data_property['image'] = asset("uploads/properties/{$property->user[0]->fullname}/{$property->nama}/{$property->bangunan_depan}");
        $data_property['url'] = route('detail-property', $property->id);
        if (empty($property)) {
            return ResponseFormatter::error(null, 'Properti tidak ditemukan.', 404);
        }

        $response = [
            'chat' => [
                'id' => $chat->id,
                'property' => $data_property,
                'sender_id' => $chat->sender_id,
                'receiver_id' => $chat->receiver_id,
                'created_at' => $chat->created_at,
                'updated_at' => $chat->updated_at,
            ],
            'messages' => $chat_details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'chat_id' => $detail->chat_id,
                    'sender_id' => $detail->sender_id,
                    'message' => $detail->message,
                    'read_at' => $detail->read_at
                        ? Carbon::parse($detail->read_at)->isToday()
                        ? Carbon::parse($detail->read_at)->format('H.i')
                        : Carbon::parse($detail->read_at)->format('d/m/Y - H.i')
                        : null,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                ];
            }),
        ];

        return ResponseFormatter::success($response);
    }
}
