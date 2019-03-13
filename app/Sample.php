<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Sample extends Model
{
    protected $collection;

    public function __construct()
    {
        if (isset($_ENV['DB_SAMPLES_COLLECTION'])) {
            $this->collection = $_ENV['DB_SAMPLES_COLLECTION'];
        } else {
            $this->collection = 'samples';
        }
    }

    public static function getSamples($f)
    {
        //Log::debug($f);
        // use the FileMapping class to translate API terms to repository terms
        // $repository_names is for any special cases that are interpreted by the service
        // $filter_to_repo is for passthrough of API terms to repository terms because service
        //   doesn't have to interpret them
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_mongo_database');
        $filter_names = FileMapping::createMappingArray('service_name', 'ir_api_input');
        $filter_types = FileMapping::createMappingArray('ir_api_input', 'ir_api_input_type');
        $filter_to_repo = FileMapping::createMappingArray('ir_api_input', 'ir_mongo_database');

        $query = new self();

        //parse over input parameters and resolve them
        //  special cases go first
        //  otherwise, ints get equals, strings get substring, arrays get in operators
        foreach ($f as $filter_name=>$filter_value) {
            //empty values count as no filter
            if (! isset($filter_value) || $filter_value == '') {
                continue;
            }

            //skip over unmapped entries
            if (! isset($filter_types[$filter_name]) || ! isset($filter_to_repo[$filter_name])) {
                continue;
            }

            //min and max age are iReceptor-specific fields to determine the range
            if ($filter_name == $filter_names['age_max']) {
                $query = $query->whereIn($repository_names['age_max'], '=>', (float) $filter_value);
                continue;
            }
            if ($filter_name == $filter_names['age_min']) {
                $query = $query->whereIn($repository_names['age_min'], '>=', (float) $filter_value);
                continue;
            }
            //sex is  a string but we want exact match here
            if ($filter_name == $filter_names['sex']) {
                $query = $query->where($repository_names['sex'] . '=', (string) $filter_value);
                continue;
            }

            // rest of the filters are done by data type
            if ($filter_types[$filter_name] == 'int') {
                $query = $query->where($filter_to_repo[$filter_name], '=', (int) $filter_value);
                continue;
            }

            if ($filter_types[$filter_name] == 'double') {
                $query = $query->where($filter_to_repo[$filter_name], '=', (float) $filter_value);
                continue;
            }

            if ($filter_types[$filter_name] == 'boolean') {
                $query = $query->where($filter_to_repo[$filter_name], '=', (bool) $filter_value);
                continue;
            }

            if ($filter_types[$filter_name] == 'array') {
                $query = $query->whereIn($filter_to_repo[$filter_name], $filter_value);
                continue;
            }

            if ($filter_types[$filter_name] == 'string') {
                $query = $query->where($filter_to_repo[$filter_name], 'like', '%' . $filter_value . '%');
                continue;
            }
        }

        $list = $query->get();

        foreach ($list as $element) {
            $element['ir_project_sample_id'] = $element['_id'];
        }

        return $list;
    }

    public static function list($params)
    {
        $l = static::all();

        return $l;
    }
}
