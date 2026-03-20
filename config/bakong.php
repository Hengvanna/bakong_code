<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bakong API JWT (from https://api-bakong.nbc.gov.kh/register)
    |--------------------------------------------------------------------------
    */
    'token' => env('BAKONG_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Use SIT (sandbox) API hosts instead of production
    |--------------------------------------------------------------------------
    */
    'use_sit' => filter_var(env('BAKONG_USE_SIT', false), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Receiver details embedded in the KHQR (must match your Bakong account)
    |--------------------------------------------------------------------------
    */
    'bakong_account_id' => env('BAKONG_ACCOUNT_ID', 'heng_vanna@bkrt'),
    'merchant_name' => env('BAKONG_MERCHANT_NAME', 'VANNA HENG'),
    'merchant_city' => env('BAKONG_MERCHANT_CITY', 'PHNOM PENH'),

    /*
    |--------------------------------------------------------------------------
    | Static KHQR (workaround for "QR expired" / Q0626 on dynamic codes)
    |--------------------------------------------------------------------------
    | When true, amount is not embedded (EMV static). The payer must enter the
    | exact amount in Bakong. Applies to all checkouts when enabled — not only
    | zero-amount orders.
    */
    'static_qr' => filter_var(env('BAKONG_KHQR_STATIC', true), FILTER_VALIDATE_BOOLEAN),

];
