<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     * Analytics (CMS-10 pada TOR).
     *
     * Kosong berarti mati. Skrip pengukuran tidak boleh ikut menyala di mesin
     * pengembang dan di server uji: kunjungan tim sendiri akan bercampur ke
     * dalam angka yang dipakai mengambil keputusan.
     *
     * Isi salah satu, bukan keduanya. Bila GTM diisi, ia yang dipakai dan GA
     * dipasang dari dalam GTM — dua pemuat sekaligus menghitung tiap kunjungan
     * dua kali.
     */
    'analytics' => [
        'ga_id' => env('ANALYTICS_GA_ID'),
        'gtm_id' => env('ANALYTICS_GTM_ID'),
    ],

];
