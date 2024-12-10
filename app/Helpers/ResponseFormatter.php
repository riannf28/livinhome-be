<?php

namespace App\Helpers;

use Carbon\Carbon;

class ResponseFormatter
{
    protected static $response = [
        'meta' => [
            'code' => 200,
            'status' => 'success',
            'message' => null,
        ],
        'data' => null,
    ];

    public static function success($data = null, $message = null, $accessToken = null, $tokenType = 'Bearer')
    {
        if (!is_array($message)) {
            $message = [$message];
        }

        self::$response['meta']['message'] = !empty($message) ? $message : 'Success';
        self::$response['data'] = $data;

        if ($accessToken) {
            self::$response['meta']['access_token'] = $accessToken;
            self::$response['meta']['token_type'] = $tokenType;
        }

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    public static function error($data = null, $message = null, $code = 400)
    {
        if (!is_array($message)) {
            $message = [$message];
        }

        self::$response['meta']['status'] = 'error';
        self::$response['meta']['code'] = $code;
        self::$response['meta']['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    public static function exception_error($data = null)
    {
        self::$response['meta']['status'] = 'error';
        self::$response['meta']['code'] = 403;
        self::$response['meta']['message'] = 'Error Exception';
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    public static function timestampToDate($timestamp)
    {
        return gmdate('Y-m-d', $timestamp);
    }

    public static function dateToTimestamp($date)
    {
        return Carbon::parse($date)->timestamp;
    }

    public static function convertPhoneNumber($phone_number)
    {
        if (substr($phone_number, 0, 1) === '0') {
            return '62' . substr($phone_number, 1);
        } elseif (substr($phone_number, 0, 2) === '62') {
            return $phone_number;
        }

        return $phone_number;
    }
}
