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
