<?php

use Illuminate\Support\Facades\Route;

Route::post('auth/register', 'AuthController@register');
Route::post('auth/regCaptcha', 'AuthController@regCaptcha');
Route::post('auth/add', 'AuthController@add');
