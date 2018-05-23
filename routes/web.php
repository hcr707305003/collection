<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/{url}', 'Caiji\CaijiController@apis');
Route::get('zuidazy', 'Caiji\resourceController@zuidazy');
Route::get('yongjiuzy', 'Caiji\resourceController@yongjiuzy');


Route::get('iqiyi', 'Caiji\IqiyiController@iqiyi');
Route::get('iqiyi/collection/content', 'Caiji\ContentController@collection_content');//指定采集
// Route::get('collection/resource', 'Caiji\ContentController@collection_resource');//视频站和资源站混合资源
Route::get('qq/collection/content', 'Caiji\ContentController@collection_qqtv');//指定腾讯视频采集
Route::get('mgtv/collection/content', 'Caiji\ContentController@collection_mgtv');//指定芒果视频采集
Route::get('iqiyi/auto/collection', 'Caiji\ContentController@auto_Collection');//自动采集


Route::get('aa', 'Caiji\ContentController@aa');//自动采集


// Route::get('iqiyi/auto/update_time', 'Caiji\ContentController@get_update');
Route::get('resource', 'Caiji\ContentController@get_all_resource');//获取的入库资源站数据
Route::get('find_name', 'Caiji\ContentController@get_find_name');//根据名称查询入库

// Route::get('a', 'Caiji\IqiyiController@iqiyi');
Route::get('{maxpage?}', 'Caiji\CaijiController@auto_apis');
Route::group([
    'prefix' => 'caiji',
    'namespace' => 'Caiji',
],function(){
   Route::get('/auto','CaijiController@apis')->name('/auto');
   Route::post('/auto','CaijiController@apis')->name('/auto');
   // Route::get('a','CaijiController@auto_apis')->name('a');
});