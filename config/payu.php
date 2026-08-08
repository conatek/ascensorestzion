<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credenciales PayU
    |--------------------------------------------------------------------------
    */
    'merchant_id' => env('PAYU_MERCHANT_ID'),
    'account_id'  => env('PAYU_ACCOUNT_ID'),
    'api_key'     => env('PAYU_API_KEY'),
    'api_login'   => env('PAYU_API_LOGIN'),

    /*
    |--------------------------------------------------------------------------
    | Entorno: sandbox | production
    |--------------------------------------------------------------------------
    */
    'environment' => env('PAYU_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Moneda y país
    |--------------------------------------------------------------------------
    */
    'currency' => env('PAYU_CURRENCY', 'COP'),
    'country'  => env('PAYU_COUNTRY', 'CO'),

    /*
    |--------------------------------------------------------------------------
    | URLs de PayU según entorno
    |--------------------------------------------------------------------------
    */
    'checkout_url' => env('PAYU_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://checkout.payulatam.com/ppp-web-gateway-payu/'
        : 'https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/',

    'api_url' => env('PAYU_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://api.payulatam.com/payments-api/4.0/service.cgi'
        : 'https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi',

    /*
    |--------------------------------------------------------------------------
    | URLs de callback
    |--------------------------------------------------------------------------
    */
    'confirmation_url' => env('PAYU_CONFIRMATION_URL'),
    'response_url'     => env('PAYU_RESPONSE_URL'),

];
