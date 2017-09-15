<?php

namespace App;

use Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    protected $table = 'sequences';

    public static function list($params)
    {

        return [];
    }

    public static function count($params)
    {
        return 0;
    }

    public static function csv($params)
    {
        set_time_limit(300);
        ini_set('memory_limit', '1G');

        $filename = sys_get_temp_dir() . '/' . uniqid() . '-' . date('Y-m-d_G-i-s', time()) . '.csv';

        $file = fopen($filename, 'w');
        fclose($file);

        return $filename;
    }
}
