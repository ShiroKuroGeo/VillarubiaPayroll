<?php

if(!function_exists('response_return')){
    function response_return(string $message = 'An error occurred.', array $data = [], int $status = 200) {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $status);
    }
}

