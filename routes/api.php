<?php

Route::any('/v2/samples', 'SampleController@index');
Route::any('/v2/sequences', 'SequenceController@index');
Route::any('/v2/analysis', 'SequenceController@analysis');
Route::any('/v2/clones', 'SequenceController@clones');
Route::any('/v2/sequences_summary', 'SequenceController@summary');
Route::any('/v2/sequences_data', 'SequenceController@data');
Route::any('/sequences', 'SequenceController@v1controls');
Route::post('deploy', 'UtilController@deploy');
Route::any('/v1/repertoire', 'SampleController@airr_repertoire');
Route::any('/v1repertoire/{repertoire_id}', 'SampleController@airr_repertoire_single');
Route::any('/v1/rearrangement', 'SequenceController@airr_rearrangement');
Route::any('/v1/rearrangement/{rearrangement_id}', 'SequenceController@airr_rearrangement_single');
