<?php

namespace App\Traits\Helpers;

trait ResponseJsonTrait{


	/**
     * Success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($message, $result = [], $code = 200) 
    {
    	$response = [
            'statut'        => "success",
            'message'       => $message,
            'data'          => $result,
            'statutCode'    => $code           
        ];

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function errorResponse($message, $result = [], $code = 500) 
    {
    	$response = [
            'statut'        => "error",
            'message'       => $message,
            'errors'        => $result,
            'statutCode'    => $code
        ];

        return response()->json($response, $code);
    }
}