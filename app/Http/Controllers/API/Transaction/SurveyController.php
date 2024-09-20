<?php

namespace App\Http\Controllers\API\Transaction;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Survey;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function submit_survey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tanggal' => 'required',
        ], [
            'property_id.required' => 'Property harus diisi.',
            'property_id.exists' => 'Property yang dipilih tidak valid.',
            'jam_mulai.required' => 'Jam mulai harus diisi.',
            'jam_mulai.date_format' => 'Jam mulai harus dalam format HH:MM.',
            'jam_selesai.required' => 'Jam selesai harus diisi.',
            'jam_selesai.date_format' => 'Jam selesai harus dalam format HH:MM.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'tanggal.required' => 'Tanggal harus diisi.',
            // 'tanggal.date' => 'Tanggal harus dalam format yang benar.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $user_id = Auth::user()->id;

            $check_data_exist_datetime = Survey::query();
            $check_data = Survey::query();


            if ($check_data->where('user_id', $user_id)->where('property_id', $request->property_id)->first()) {
                return ResponseFormatter::error(null, 'Data survey sudah ada', 404);
            }

            if ($check_data_exist_datetime->where('property_id', $request->property_id)->where('tanggal', $request->tanggal)->where('jam_mulai', Carbon::createFromFormat('H:i', $request->jam_mulai)->format('H:i:s'))->first()) {
                return ResponseFormatter::error(null, "Data survey pada tanggal {$request->tanggal} dan jam {$request->jam_mulai} sudah tersedia, silahkan ganti waktu survey", 404);
            }

            $data = new Survey();
            $data->fill($request->all());
            $data->tanggal = ResponseFormatter::timestampToDate($request->tanggal);
            $data->user_id = $user_id;
            $data->save();

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function edit_submit_survey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
            'property_id' => 'required|exists:properties,id',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tanggal' => 'required',
        ], [
            'survey_id.required' => 'Survey harus diisi.',
            'survey_id.exists' => 'Survey yang dipilih tidak valid.',
            'property_id.required' => 'Property harus diisi.',
            'property_id.exists' => 'Property yang dipilih tidak valid.',
            'jam_mulai.required' => 'Jam mulai harus diisi.',
            'jam_mulai.date_format' => 'Jam mulai harus dalam format HH:MM.',
            'jam_selesai.required' => 'Jam selesai harus diisi.',
            'jam_selesai.date_format' => 'Jam selesai harus dalam format HH:MM.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'tanggal.required' => 'Tanggal harus diisi.',
            // 'tanggal.date' => 'Tanggal harus dalam format yang benar.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $user = Auth::user();

            $check_exist_data = Survey::where('user_id', $user->id)->where('property_id', $request->property_id)->where('tanggal', $request->tanggal)->first();
            $check_data_exist_datetime = Survey::query();

            if (empty($check_exist_data)) {
                return ResponseFormatter::error(null, "Data survey bukan dari user {$user->fullname}", 404);
            }

            if ($check_data_exist_datetime->where('user_id', '!=', $user->id)->where('property_id', $request->property_id)->where('tanggal', $request->tanggal)->where('jam_mulai', Carbon::createFromFormat('H:i', $request->jam_mulai)->format('H:i:s'))->first()) {
                return ResponseFormatter::error(null, "Data survey pada tanggal {$request->tanggal} dan jam {$request->jam_mulai} sudah tersedia, silahkan ganti waktu survey", 404);
            }

            $data = Survey::findOrFail($request->survey_id);

            if ($data->is_cancel) {
                return ResponseFormatter::error(null, "Data survey sudah dibatalkan", 400);
            }

            unset($request->survey_id);
            $data->fill($request->all());
            $data->tanggal = ResponseFormatter::timestampToDate($request->tanggal);
            $data->user_id = $user->id;
            $data->save();
            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function cancel_survey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
            'reason_id' => 'required|exists:reason_cancels,id'
        ], [
            'survey_id.required' => 'Survey harus diisi.',
            'survey_id.exists' => 'Survey yang dipilih tidak valid.',
            'reason_id.required' => 'Alasan harus diisi.',
            'survey_id.exists' => 'Alasan yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $data = Survey::where('id', $request->survey_id)->where('user_id', Auth::user()->id)->first();
            if (empty($data)) {
                return ResponseFormatter::error(null, "Not Found", 404);
            }
            $data->is_cancel = true;
            $data->reason_id = $request->reason_id;
            $data->save();

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }
}
