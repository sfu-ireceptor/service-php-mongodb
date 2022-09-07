<?php

Route::get('/', function () {

    // if there is an override file
    if (file_exists(public_path() . '/home/index.html')) {
        return redirect('/home');
    }

    return view('home');
});
