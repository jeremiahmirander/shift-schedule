<?php

// Auth
Route::post('register', 'AuthController@register')->name('register');
Route::post('login', 'AuthController@login')->name('login');
Route::post('password/forgot', 'AuthController@forgot')->name('forgot');
Route::post('password/reset', 'AuthController@reset')->name('reset');
Route::post('logout', 'AuthController@logout')->name('logout');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('profile', 'UserController@me')->name('profile');
    Route::patch('profile', 'UserController@update');
    Route::get('me', 'UserController@me')->name('me');

    // Lists
    Route::get('lists', 'TaskListController@index')->name('lists');
    Route::post('lists', 'TaskListController@create');
    Route::get('lists/{list}', 'TaskListController@show')->name('list');
    Route::patch('lists/{list}', 'TaskListController@update');
    Route::delete('lists/{list}', 'TaskListController@destroy');

    // Tasks
    Route::post('lists/{list}', 'TaskController@create');
    Route::patch('lists/{list}/{task}', 'TaskController@update')->name('task');
    Route::delete('lists/{list}/{task}', 'TaskController@delete');
});
