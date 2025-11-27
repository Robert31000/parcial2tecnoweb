<?php

return [
    'base_url'       => env('PAGOFACIL_BASE_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2'),
    'token_service'  => env('PAGOFACIL_TOKEN_SERVICE'),
    'token_secret'   => env('PAGOFACIL_TOKEN_SECRET'),
    'client_code'    => env('PAGOFACIL_CLIENT_CODE', 'LAVANDERIA_BELEN'),
    'callback_url'   => env('PAGOFACIL_CALLBACK_URL'),
    'return_url'     => env('PAGOFACIL_RETURN_URL'),
    
    // Monto mínimo para pruebas (0.10 Bs)
    'test_amount'    => env('PAGOFACIL_TEST_AMOUNT', 0.10),
];
