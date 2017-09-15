<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    protected $table = 'samples';

    public static function list($params)
    {
        return [];
    }
}
