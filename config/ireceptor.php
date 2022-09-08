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

    /*
    |--------------------------------------------------------------------------
    | ADC Info object
    |--------------------------------------------------------------------------
    | Variables used in Info portion of ADC API response
    | More info:
    | https://github.com/airr-community/airr-standards/blob/master/specs/airr-schema.yaml
    */

    'airr_info_title' => env('AIRR-INFO-TITLE', 'iReceptor Service'),
    'airr_info_desc' => env('AIRR_INFO_DESC', 'AIRR Data Commons API for iReceptor'),
    'airr_info_version' => env('AIRR_INFO_VERSION', '3.0'),
    'airr_info_contact_name' => env('AIRR_INFO_CONTACT_NAME', 'iReceptor support'),
    'airr_info_contact_url' => env('AIRR_INFO_CONTACT_URL', 'http://www.ireceptor.org'),
    'airr_info_contact_email' => env('AIRR_INFO_CONTACT_EMAIL', 'support@ireceptor.org'),
    'airr_info_license_name' => env('AIRR_INFO_LICENSE_NAME', 'GNU LGPL V3'),
    'airr_info_api_title' => env('AIRR_INFO_API_TITLE', 'AIRR Data Commons API'),
    'airr_info_api_version' => env('AIRR_INFO_API_VERSION', '1.2.0'),
    'airr_info_api_contact_name' => env('AIRR_INFO_API_CONTACT_NAME', 'AIRR Community'),
    'airr_info_api_contact_url' => env('AIRR_INFO_API_CONTACT_URL', 'https://www.antibodysociety.org/the-airr-community/'),
    'airr_info_api_contact_email' => env('AIRR_INFO_API_CONTACT_EMAIL', 'join@airr-community.org'),
    'airr_info_api_contact_desc' => env('AIRR_INFO_API_CONTACT_DESC', 'Major Version 1 of the Adaptive Immune Receptor Repertoire (AIRR) data repository web service application programming interface (API).'),
    'airr_info_schema_title' => env('AIRR_INFO_SCHEMA_TITLE', 'AIRR Schema'),
    'airr_info_schema_desc' => env('AIRR_INFO_SCHEMA_DESC', 'Schema definitions for AIRR standards objects'),
    'airr_info_schema_version' => env('AIRR_INFO_SCHEMA_VERSION', '1.4.0'),
    'airr_info_schema_contact_name' => env('AIRR_INFO_SCHEMA_CONTACT_NAME', 'AIRR Community'),
    'airr_info_schema_contact_url' => env('AIRR_INFO_SCHEMA_CONTACT_URL', 'https://github.com/airr-community'),
];
