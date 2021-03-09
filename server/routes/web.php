<?php

Route::get('/{any?}', 'FrontendController@app')
    ->where('any', '.*')
    ->name('app');
