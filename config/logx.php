<?php

return [
    'enable' => env('LOGX_ENABLE', true),
    'daily'  => env('LOGX_DAILY', true),
    /*
    |--------------------------------------------------------------------------
    | ip filter
    |--------------------------------------------------------------------------
    |
    | Specify the IP address that can be recorded. If it is empty, all the ip will be recorded
    |
    | Example
    | include : 202.12.12.1,212.11.102.12
    | exclude : 202.12.12.1,212.11.102.12
    */
    'ip'     => [
        'include' => array_filter(explode(',', env('LOGX_IP_INCLUDE', ''))),
        'exclude' => array_filter(explode(',', env('LOGX_IP_EXCLUDE', ''))),
    ],
    /*
    |--------------------------------------------------------------------------
    | method filter
    |--------------------------------------------------------------------------
    |
    | Specify the class or method that can be recorded. If it is empty, all the records will be recorded
    |
    | Example
    | include : TestController,TestController::*,TestController::index,*::getUser
    | exclude : TestController,TestController::*,TestController::index,*::getUser
    */
    'method'  => [
        'include' => array_filter(explode(',', env('LOGX_METHOD_INCLUDE', ''))),
        'exclude' => array_filter(explode(',', env('LOGX_METHOD_EXCLUDE', ''))),
    ],
];