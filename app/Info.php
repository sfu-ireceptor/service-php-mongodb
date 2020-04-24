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

    public function getLastUpdate()
    {
        $query = new self;
        $result = $query->get()->max('last_update');

        return $result;
    }
}
