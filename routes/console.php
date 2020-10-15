<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('test:intersect', function () {
    $data1  = [
        'col1'  => true,
        'col2'  => true,
        'col3'  => true,
        'col4'  => true
    ];

    $data2  = [
        'col2'  => true
    ];

    print_r(array_intersect($data1, $data2));
    print_r(array_intersect($data2, $data1));


})->describe('test array intersect');
