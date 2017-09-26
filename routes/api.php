<?php

Route::any('samples', 'SampleController@index');
Route::any('sequences', 'SequenceController@index');
Route::any('analysis', 'SequenceController@analysis');
Route::any('clones', 'SequenceController@clones');
Route::any('sequences_summary', 'SequenceController@summary');

Route::post('deploy', 'UtilController@deploy');
