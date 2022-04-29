<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

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
        if (isset($_ENV['AIRR_INFO_TITLE'])) {
            $this->airr_info_title = $_ENV['AIRR_INFO_TITLE'];
        } else {
            $this->airr_info_title = 'airr-api-ireceptor';
        }
        if (isset($_ENV['AIRR_INFO_DESC'])) {
            $this->airr_info_desc = $_ENV['AIRR_INFO_DESC'];
        } else {
            $this->airr_info_desc = 'AIRR Data Commons API for iReceptor';
        }
        if (isset($_ENV['AIRR_INFO_VERSION'])) {
            $this->airr_info_version = $_ENV['AIRR_INFO_VERSION'];
        } else {
            $this->airr_info_version = '3.0';
        }
        if (isset($_ENV['AIRR_INFO_LAST_UPDATE'])) {
            $this->airr_info_last_update = $_ENV['AIRR_INFO_LAST_UPDATE'];
        } else {
            $this->airr_info_last_update = null;
        }
        if (isset($_ENV['AIRR_INFO_CONTACT_NAME'])) {
            $this->airr_info_contact_name = $_ENV['AIRR_INFO_CONTACT_NAME'];
        } else {
            $this->airr_info_contact_name = 'iReceptor';
        }
        if (isset($_ENV['AIRR_INFO_CONTACT_URL'])) {
            $this->airr_info_contact_url = $_ENV['AIRR_INFO_CONTACT_URL'];
        } else {
            $this->airr_info_contact_url = 'http://www.ireceptor.org';
        }
        if (isset($_ENV['AIRR_INFO_CONTACT_EMAIL'])) {
            $this->airr_info_contact_email = $_ENV['AIRR_INFO_CONTACT_EMAIL'];
        } else {
            $this->airr_info_contact_email = 'support@ireceptor.org';
        }
        if (isset($_ENV['AIRR_INFO_LICENSE_NAME'])) {
            $this->airr_info_license_name = $_ENV['AIRR_INFO_LICENSE_NAME'];
        } else {
            $this->airr_info_license_name = 'GNU LGPL V3';
        }

        if (isset($_ENV['AIRR_INFO_API_TITLE'])) {
            $this->airr_info_api_title = $_ENV['AIRR_INFO_API_TITLE'];
        } else {
            $this->airr_info_api_title = 'AIRR Data Commons API';
        }
        if (isset($_ENV['AIRR_INFO_API_VERSION'])) {
            $this->airr_info_api_version = $_ENV['AIRR_INFO_API_VERSION'];
        } else {
            $this->airr_info_api_version = '1.0';
        }
        if (isset($_ENV['AIRR_INFO_API_CONTACT_NAME'])) {
            $this->airr_info_api_contact_name = $_ENV['AIRR_INFO_API_CONTACT_NAME'];
        } else {
            $this->airr_info_api_contact_name = 'AIRR Community';
        }
        if (isset($_ENV['AIRR_INFO_API_CONTACT_URL'])) {
            $this->airr_info_api_contact_url = $_ENV['AIRR_INFO_API_CONTACT_URL'];
        } else {
            $this->airr_info_api_contact_url = 'http://www.airr-community.org/';
        }
        if (isset($_ENV['AIRR_INFO_API_CONTACT_EMAIL'])) {
            $this->airr_info_api_contact_email = $_ENV['AIRR_INFO_API_CONTACT_EMAIL'];
        } else {
            $this->airr_info_api_contact_email = 'join@airr-community.org';
        }
        if (isset($_ENV['AIRR_INFO_API_CONTACT_DESC'])) {
            $this->airr_info_api_contact_desc = $_ENV['AIRR_INFO_API_CONTACT_DESC'];
        } else {
            $this->airr_info_api_contact_desc = 'Major Version 1 of the Adaptive Immune Receptor Repertoire (AIRR) data repository web service application programming interface (API).';
        }

        if (isset($_ENV['AIRR_INFO_SCHEMA_TITLE'])) {
            $this->airr_info_schema_title = $_ENV['AIRR_INFO_SCHEMA_TITLE'];
        } else {
            $this->airr_info_schema_title = 'AIRR Schema';
        }
        if (isset($_ENV['AIRR_INFO_SCHEMA_DESC'])) {
            $this->airr_info_schema_desc = $_ENV['AIRR_INFO_SCHEMA_DESC'];
        } else {
            $this->airr_info_schema_desc = 'Schema definitions for AIRR standards objects';
        }
        if (isset($_ENV['AIRR_INFO_SCHEMA_VERSION'])) {
            $this->airr_info_schema_version = $_ENV['AIRR_INFO_SCHEMA_VERSION'];
        } else {
            $this->airr_info_schema_version = '1.3';
        }
        if (isset($_ENV['AIRR_INFO_SCHEMA_CONTACT_NAME'])) {
            $this->airr_info_schema_contact_name = $_ENV['AIRR_INFO_SCHEMA_CONTACT_NAME'];
        } else {
            $this->airr_info_schema_contact_name = 'AIRR Community';
        }
        if (isset($_ENV['AIRR_INFO_SCHEMA_CONTACT_URL'])) {
            $this->airr_info_schema_contact_url = $_ENV['AIRR_INFO_SCHEMA_CONTACT_URL'];
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
