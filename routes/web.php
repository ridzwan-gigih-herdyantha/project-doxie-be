<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ready', function () {
    $checks = [
        'database' => false,
        'redis'    => false,
    ];

    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (\Exception $e) {
        $checks['database'] = false;
    }

    try {
        Redis::ping();
        $checks['redis'] = true;
    } catch (\Exception $e) {
        $checks['redis'] = false;
    }

    $allReady = collect($checks)->every(fn ($status) => $status === true);

    return response()->json([
        'status' => $allReady ? 'ok' : 'degraded',
        'checks' => $checks,
    ], $allReady ? 200 : 503);
});
