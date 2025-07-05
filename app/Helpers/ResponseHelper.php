<?php

if (!function_exists('resJSON')) {
    function resJSON($status, $message, $data, $code, $extra = [],$modeDev = false)
    {
        if(($code == 500 || $code == 0) && !$modeDev) {
            $message = "Terjadi kesalahan server silahkan coba lagi!";
            $code = 500;
        } else if ($code == 0) {
            $code = 500;
        }

        $response = [
            'status' => $status,
            'message'=> $message,
            'data' => $data
        ];

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        };

        if(empty($data)){
        unset($response['data']);
        };

        return response()->json($response, $code);

        // return response()->json([
        //     'status' => $status,
        //     'message'=> $message,
        //     'data' => $data
        // ],$code);
    }
}
