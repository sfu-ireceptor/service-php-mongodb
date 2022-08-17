<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use ireceptor;

class Info extends Model
{
    protected $collection;

    public function __construct()
    {
        if (isset($_ENV['DB_INFO_COLLECTION'])) {
            $this->collection = $_ENV['DB_INFO_COLLECTION'];
        } else {
            $this->collection = 'info';
        }

        // get AIRR Info: sections from the .env file if set,
        //  use defaults otherwise
        if (config('ireceptor.airr_info_title')!= null) {
            $this->airr_info_title = config('ireceptor.airr_info_title');
        } else {
            $this->airr_info_title = 'airr-api-ireceptor';
        }
        if (config('ireceptor.airr_info_desc')!= null) {
            $this->airr_info_desc = config('ireceptor.airr_info_desc');
        } else {
            $this->airr_info_desc = 'AIRR Data Commons API for iReceptor';
        }
        if (config('ireceptor.airr_info_version')!= null) {
            $this->airr_info_version = config('ireceptor.airr_info_version');
        } else {
            $this->airr_info_version = '3.0';
        }
        if (config('ireceptor.airr_info_last_update')!= null) {
            $this->airr_info_last_update = config('ireceptor.airr_info_last_update');
        } else {
            $this->airr_info_last_update = null;
        }
        if (config('ireceptor.airr_info_contact_name')!= null) {
            $this->airr_info_contact_name = config('ireceptor.airr_info_contact_name');
        } else {
            $this->airr_info_contact_name = 'iReceptor';
        }
        if (config('ireceptor.airr_info_contact_url')!= null) {
            $this->airr_info_contact_url = config('ireceptor.airr_info_contact_url');
        } else {
            $this->airr_info_contact_url = 'http://www.ireceptor.org';
        }
        if (config('ireceptor.airr_info_contact_email')!= null) {
            $this->airr_info_contact_email = config('ireceptor.airr_info_contact_email');
        } else {
            $this->airr_info_contact_email = 'support@ireceptor.org';
        }
        if (config('ireceptor.airr_info_license_name')!= null) {
            $this->airr_info_license_name = config('ireceptor.airr_info_license_name');
        } else {
            $this->airr_info_license_name = 'GNU LGPL V3';
        }

        if (config('ireceptor.airr_info_api_title')!= null) {
            $this->airr_info_api_title = config('ireceptor.airr_info_api_title');
        } else {
            $this->airr_info_api_title = 'AIRR Data Commons API';
        }
        if (config('ireceptor.airr_info_api_version')!= null) {
            $this->airr_info_api_version = config('ireceptor.airr_info_api_version');
        } else {
            $this->airr_info_api_version = '1.2.0';
        }
        if (config('ireceptor.airr_info_api_contact_name')!= null) {
            $this->airr_info_api_contact_name = config('ireceptor.airr_info_api_contact_name');
        } else {
            $this->airr_info_api_contact_name = 'AIRR Community';
        }
        if (config('ireceptor.airr_info_api_contact_url')!= null) {
            $this->airr_info_api_contact_url = config('ireceptor.airr_info_api_contact_url');
        } else {
            $this->airr_info_api_contact_url = 'http://www.airr-community.org/';
        }
        if (config('ireceptor.airr_info_api_contact_email')!= null) {
            $this->airr_info_api_contact_email = config('ireceptor.airr_info_api_contact_email');
        } else {
            $this->airr_info_api_contact_email = 'join@airr-community.org';
        }
        if (config('ireceptor.airr_info_api_contact_desc')!= null) {
            $this->airr_info_api_contact_desc = config('ireceptor.airr_info_api_contact_desc');
        } else {
            $this->airr_info_api_contact_desc = 'Major Version 1 of the Adaptive Immune Receptor Repertoire (AIRR) data repository web service application programming interface (API).';
        }

        if (config('ireceptor.airr_info_schema_title')!= null) {
            $this->airr_info_schema_title = config('ireceptor.airr_info_schema_title');
        } else {
            $this->airr_info_schema_title = 'AIRR Schema';
        }
        if (config('ireceptor.airr_info_schema_desc')!= null) {
            $this->airr_info_schema_desc = config('ireceptor.airr_info_schema_desc');
        } else {
            $this->airr_info_schema_desc = 'Schema definitions for AIRR standards objects';
        }
        if (config('ireceptor.airr_info_schema_version')!= null) {
            $this->airr_info_schema_version = config('ireceptor.airr_info_schema_version');
        } else {
            $this->airr_info_schema_version = '1.4.0';
        }
        if (config('ireceptor.airr_info_schema_contact_name')!= null) {
            $this->airr_info_schema_contact_name = config('ireceptor.airr_info_schema_contact_name');
        } else {
            $this->airr_info_schema_contact_name = 'AIRR Community';
        }
        if (config('ireceptor.airr_info_schema_contact_url')!= null) {
            $this->airr_info_schema_contact_url = config('ireceptor.airr_info_schema_contact_url');
        } else {
            $this->airr_info_schema_contact_url = 'https://github.com/airr-community';
        }
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public static function getLastUpdate()
    {
        $query = new self;
        $result = $query->get()->max('last_update');

        return $result;
    }

    public static function getAirrInfo()
    {
        //return the Info object used by the ADC API
        $info = new self();
        $response = [];

        $response['title'] = $info->airr_info_title;
        $response['description'] = $info->airr_info_desc;
        $response['version'] = $info->airr_info_version;
        $response['last_update'] = $info->airr_info_last_update;

        $response['contact']['name'] = $info->airr_info_contact_name;
        $response['contact']['url'] = $info->airr_info_contact_url;
        $response['contact']['email'] = $info->airr_info_contact_email;

        $response['license']['name'] = $info->airr_info_license_name;

        $response['api']['title'] = $info->airr_info_api_title;
        $response['api']['version'] = $info->airr_info_api_version;
        $response['api']['contact']['name'] = $info->airr_info_api_contact_name;
        $response['api']['contact']['url'] = $info->airr_info_api_contact_url;
        $response['api']['contact']['email'] = $info->airr_info_api_contact_email;
        $response['api']['contact']['description'] = $info->airr_info_api_contact_desc;

        $response['schema']['title'] = $info->airr_info_schema_title;
        $response['schema']['description'] = $info->airr_info_schema_desc;
        $response['schema']['version'] = $info->airr_info_schema_version;
        $response['schema']['contact']['name'] = $info->airr_info_schema_contact_name;
        $response['schema']['contact']['url'] = $info->airr_info_schema_contact_url;

        return $response;
    }

    public static function getIrPlusInfo_stats()
    {
        //return the Info object used by iReceptor+ stats API
        $response = [];

        $response['title'] = 'iReceptorPlus Statistics API';
        $response['version'] = '0.3.0';
        $response['description'] = 'Statistics API for the iReceptor Plus platform.';
        $response['contact'] = null;

        return $response;
    }
}
