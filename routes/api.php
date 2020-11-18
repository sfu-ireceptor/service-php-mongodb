<?php

Route::any('/v2/samples', 'SampleController@index');
Route::any('/v2/sequences', 'SequenceController@index');
Route::any('/v2/analysis', 'SequenceController@analysis');
Route::any('/v2/clones', 'SequenceController@clones');
Route::any('/v2/sequences_summary', 'SequenceController@summary');
Route::any('/v2/sequences_data', 'SequenceController@data');
Route::any('/sequences', 'SequenceController@v1controls');
Route::post('deploy', 'UtilController@deploy');
Route::any('/airr/v1/', 'AirrApiController@index');
Route::any('/airr/v1/info', 'AirrApiController@info');
Route::any('/airr/v1/swagger', 'AirrApiController@swagger');
Route::any('/airr/v1/repertoire', 'AirrApiController@airr_repertoire');
Route::any('/airr/v1/repertoire/{repertoire_id}', 'AirrApiController@airr_repertoire_single');
Route::any('/airr/v1/rearrangement', 'AirrApiController@airr_rearrangement');
Route::any('/airr/v1/rearrangement/{rearrangement_id}', 'AirrApiController@airr_rearrangement_single');
Route::any('/airr/v1/clone', 'AirrApiController@airr_clone');
Route::any('/airr/v1/clone/{clone_id}', 'AirrApiController@airr_clone_single');