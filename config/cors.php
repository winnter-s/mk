<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    //'paths' => ['api/*'],
    'paths' => ['wx/*'], // 允许跨域访问的前缀

    'allowed_methods' => ['*'], // 方法

    'allowed_origins' => ['*'], // 请求源

    'allowed_origins_patterns' => [], // 请求源 正则

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 1800, // 预加载保留时间 ， 1800 秒内无需再次预加载

    'supports_credentials' => false,

];
