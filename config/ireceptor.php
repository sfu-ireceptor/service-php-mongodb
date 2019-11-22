<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Provenance URL
    |--------------------------------------------------------------------------
    |
    | Provenance URL for this iReceptor repository
    | Ex: http://ireceptor.irmacs.sfu.ca/repositories/ipa1
    |
    */

    // default
    'provenance_url' => env('IRECEPTOR_PROVENANCE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | AIRR Mapping file
    |--------------------------------------------------------------------------
    |
    | iReceptor AIRR Mapping Configuration File
    | More info: https://github.com/sfu-ireceptor/config
    |
    */

    // default
    'airr_mapping_file' => env('IRECEPTOR_AIRR_MAPPING_FILE', 'AIRR-iReceptorMapping.txt'),
];
