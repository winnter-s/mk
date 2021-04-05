<?php

use Illuminate\Support\Facades\Route;

# 用户模块-用户
Route::post('auth/register', 'AuthController@register'); //账号注册
Route::post('auth/regCaptcha', 'AuthController@regCaptcha'); //注册验证码
Route::post('auth/login', 'AuthController@login'); //账号登陆
Route::get('auth/info', 'AuthController@info'); //用户信息
Route::post('auth/logout', 'AuthController@logout'); //账号登出
Route::post('auth/profile', 'AuthController@profile'); //账号修改
Route::post('auth/reset', 'AuthController@reset'); //账号密码重置
Route::post('auth/captcha', 'AuthController@regCaptcha'); //验证码

# 用户模块-地址
Route::get('address/list','AddressController@list'); //收货地址列表
Route::get('address/detail','AddressController@detail'); //收获地址详情
Route::post('address/save','AddressController@save'); //保存收货地址
Route::post('address/delete','AddressController@delete'); //删除收货地址

# 商品模块-类目
Route::get('catalog/index','CatalogController@index'); //分类目录全部分类数据接口
Route::get('catalog/current','CatalogController@current'); //分类目录当前分类数据接口

# 商品模块-品牌
Route::get('brand/list','BrandController@list'); //品牌列表
Route::get('brand/detail','BrandController@detail'); //品牌详情

