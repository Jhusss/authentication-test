<?php

namespace App\Helpers;


class ResponseHelper {
    public static function make($status = 200, $message = null, $data = []){
        return $status != 422 ? response()->json([
            'message' => $message,
            'result' => $data
        ], $status) :
        response()->json([
            'message' => $message,
            'errors' => $data
        ], $status);
    }
}