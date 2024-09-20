<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Survey;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function calon_penyewa()
    {
        try {
            $data = Survey::with('user', 'property')->whereNull('status')->where('is_cancel', false)->get();
            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function tolak_survey()
    {
        try {
            $data = Survey::with('user', 'property')->where('status', false)->where('is_cancel', false)->get();

            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function confirm_survey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
        ], [
            'survey_id.required' => 'Survey harus diisi.',
            'survey_id.exists' => 'Survey yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $data = Survey::where('id', $request->survey_id)->where('is_cancel', false)->first();
            $data->status = true;
            $data->save();

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function reject_survey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
        ], [
            'survey_id.required' => 'Survey harus diisi.',
            'survey_id.exists' => 'Survey yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $data = Survey::where('id', $request->survey_id)->where('is_cancel', false)->first();
            $data->status = false;
            $data->save();

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }
}
