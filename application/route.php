<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::resource('orders','order/index');
Route::resource('index','index/index');
Route::resource('users','user/index');
Route::get('rank/:method','user/rank/:method');
//Route::resource('api/:version/:controller','api/:version.:controller');
// Route::resource('blogs','index/blog');
//return [
	// '__pattern__' => [
	// 	'name' => '\w+',
	// ],
	//'hello/[:name]' => 'index/index/hello',
	// '[hello]' => [
	// 	':id' => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
	// 	':name' => ['index/hello', ['method' => 'post']],
	// ],
	
	
//];

// 
