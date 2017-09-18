<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Sample extends Model
{
    protected $collection = 'samples';

    public static function list($params)
    {
    	$l = static::all();
    	
        return $l;
    }
}
