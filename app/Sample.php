<?php

namespace App;

use Illuminate\Support\Facades\DB;
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

    public function getCollection()
    {
        return $this->collection;
    }

    public static function processAirrFilter($f, $service_to_airr_array, $airr_types_array)
    {
        if (! (isset($f['op'])) || $f['op'] == '') {
            return;
        }
        if (! (isset($f['content'])) || $f['content'] == '') {
            return;
        }
        $field = '';
        $type = '';

        $content = $f['content'];
        $operator = $f['op'];

        if (isset($content['field']) && $content['field'] != '') {
            // fields are of form sample.subject.subject_id
            //   we only want the last part, as it will define the field in database
            $field_array = explode('.', $content['field']);
            $field = end($field_array);

            $type = $airr_types_array[$field];
        }

        if ($type == '' && $field != '') {
            Log::error("Type not found $field");
        }

        if (isset($content['value']) && $content['value'] != '') {
            switch ($type) {
                case 'integer':
                case 'number':
                case 'boolean':
                    if (is_array($content['value'])) {
                        $value = json_encode($content['value']);
                    } else {
                        $value = $content['value'];
                    }
                    break;
                case 'string':
                default:
                    if (is_array($content['value'])) {
                        $value = json_encode($content['value']);
                    } else {
                        $value = '"' . $content['value'] . '"';
                    }
                    break;
            }
        }

        switch ($f['op']) {
            case '=':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":' . $value . '}';
                } else {
                    return;
                }
            case '!=':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$ne":"' . $value . '"}}';
                } else {
                    return;
                }
            case '<':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$lt":"' . $value . '"}}';
                } else {
                    return;
                }
            case '>':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$gt":"' . $value . '"}}';
                } else {
                    return;
                }
            case '<=':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$le":"' . $value . '"}}';
                } else {
                    return;
                }
            case '>=':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$ge":"' . $value . '"}}';
                } else {
                    return;
                }
            case 'contains':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$regex":"' . $value . '","$options":"i"}}';
                } else {
                    return;
                }
            case 'is':
                if ($field != '') {
                    return '{"' . $field . '":{"$exists":"false"}}';
                } else {
                    return;
                }
            case 'not':
                if ($field != '') {
                    return '{"' . $field . '":{"$exists":"true"}}';
                } else {
                    return;
                }
            case 'in':
                if ($field != '' && $value != '' && is_array($value)) {
                    return '{"' . $field . '":{"$in":"' . $value . '"}}';
                } else {
                    return;
                }
            case 'exclude':
                if ($field != '' && $value != '' && is_array($value)) {
                    return '{"' . $field . '":{"$nin":"' . $value . '"}}';
                } else {
                    return;
                }
            case 'and':
                if (is_array($content) && count($content) > 1) {
                    $exp_list = [];
                    foreach ($content as $content_chunk) {
                        $exp = self::processAirrFilter($content_chunk, $service_to_airr_array, $airr_types_array);
                        if (isset($exp)) {
                            array_push($exp_list, $exp);
                        } else {
                            return;
                        }
                    }

                    return '{"$and":[' . implode(',', $exp_list) . ']}';
                } else {
                    return;
                }
            case 'or':
                if (is_array($content) && count($content) > 1) {
                    $exp_list = [];
                    foreach ($content as $content_chunk) {
                        $exp = self::processAirrFilter($content_chunk, $service_to_airr_array, $airr_types_array);
                        if (isset($exp)) {
                            array_push($exp_list, $exp);
                        } else {
                            return;
                        }
                    }

                    return '{"$or":[' . implode($exp_list) . ']}';
                } else {
                    return;
                }
            default:
                Log::error('Unknown op');

                return;
        } //end switch ($op)

        // should not get here
    }

    public static function airrRepertoireRequest($params)
    {
        //function that processes AIRR API request and returns a response
        //  currently the response is iReceptor API response
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_mongo_database');
        $airr_names = FileMapping::createMappingArray('service_name', 'airr');
        $airr_to_repository = FileMapping::createMappingArray('airr', 'ir_mongo_database');
        $airr_types = FileMapping::createMappingArray('airr', 'airr_type');

        $query_string = '{}';
        $options = [];
        $fields_to_retrieve = [];
        $query = new self();
        // if we have filters, process them
        if (isset($params['filters']) && $params['filters'] != '') {
            $query_string = self::processAirrFilter($params['filters'], $airr_names, $airr_types);
            if ($query_string == null) {
                return;
            }
        }
        // if fields parameter is set, we only want to return the fields specified
        if (isset($params['fields']) && $params['fields'] != '') {
            foreach ($params['fields'] as $airr_field_name) {
                if (isset($airr_to_repository[$airr_field_name]) && $airr_to_repository[$airr_field_name] != '') {
                    $fields_to_retrieve[$airr_to_repository[$airr_field_name]] = 1;
                }
            }
            $options['projection'] = $fields_to_retrieve;
        }
        // if we have from parameter, start the query at that value
        if (isset($params['from']) && is_int($params['from'])) {
            $options['skip'] = abs($params['from']);
        }

        // if we have size parameter, don't take more than that number of results
        if (isset($params['size']) && is_int($params['size'])) {
            $options['limit'] = abs($params['size']);
        }

        //echo "<br/>\n Returning $query_string";
        //return ($query_string);

        //if facets is set we want to aggregate by that fields using the sum operation
        if (isset($params['facets']) && $params['facets'] != '') {
            $aggOptions = [];
            $aggOptions[0]['$match'] = json_decode($query_string);
            $aggOptions[1]['$group'] = ['_id'=> [$airr_to_repository[$params['facets']] => '$' . $airr_to_repository[$params['facets']]]];
            $aggOptions[1]['$group']['count'] = ['$sum' => 1];

            $list = DB::collection($query->getCollection())->raw()->aggregate($aggOptions);
        // $list = DB::collection($query->getCollection())->raw()->find(json_decode($query_string, true), $options);
        } else {
            $list = DB::collection($query->getCollection())->raw()->find(json_decode($query_string, true), $options);
        }

        return $list->toArray();
    }

    public static function airrRepertoireResponse($response_list)
    {
        //method that takes an array of AIRR terms and returns a JSON string
        //  that represents a repertoire response as defined in AIRR API

        //first, we need some mappings to convert database values to AIRR terms
        //  and bucket them into appropriate AIRR classes
        $airr_classes = FileMapping::createMappingArray('ir_mongo_database', 'airr_full_path', ['ir_class'=>['repertoire', 'ir_repertoire']]);
        $db_names = FileMapping::createMappingArray('service_name', 'ir_mongo_database', ['ir_class'=>['repertoire', 'ir_repertoire']]);
        $airr_names = FileMapping::createMappingArray('service_name', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire']]);
        $repository_to_airr = FileMapping::createMappingArray('ir_mongo_database', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire']]);

        //each iReceptor 'sample' is an AIRR repertoire consisting of a single sample and  a single rearrangement set
        //  associated with it, so we will take the array of samples and place each element into an appropriate section
        //  of AIRR reperotoire response

        $return_list = [];
        foreach ($response_list as $repertoire) {
            $return_array = [];

            foreach ($repertoire as $return_key => $return_element) {
                if (isset($airr_classes[$return_key]) && $airr_classes[$return_key] != '') {
                    //$key_array =  $airr_classes[$return_key].".".$repository_to_airr[$return_key];
                    array_set($return_array, $airr_classes[$return_key], $return_element);
                    //$return_array=[$repository_to_airr[$return_key] => $return_element];
                }
            }

            $return_list[] = $return_array;
        }

        return $return_list;
        //return (json_encode($return_list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    public static function airrRepertoireFacetsResponse($response_list)
    {
        $return_array = [];
        //MongoDB by default aggregates in the format _id: {column: value}, count: sum
        //  AIRR expects {column: value, count: sum} {column: value2, count: sum}
        foreach ($response_list as $response) {
            $temp = [];
            $facet = $response['_id'];
            $count = $response['count'];
            $temp[key($facet)] = $facet[key($facet)];
            $temp['count'] = $count;
            $return_array[] = $temp;
        }

        return $return_array;
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
        $repo_to_output = FileMapping::createMappingArray('ir_mongo_database', 'ir_api_output', ['ir_class'=>['repertoire', 'ir_repertoire']]);

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
                $query = $query->where($repository_names['age_max'], '<=', (float) $filter_value);
                continue;
            }
            if ($filter_name == $filter_names['age_min']) {
                $query = $query->where($repository_names['age_min'], '>=', (float) $filter_value);
                continue;
            }
            //sex is  a string but we want exact match here
            if ($filter_name == $filter_names['sex']) {
                $query = $query->where($repository_names['sex'], 'like', (string) $filter_value);
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

        $list = $query->get()->toArray();
        $return_array = [];
        foreach ($list as $element) {

            //if there's a mapping for any return value, replace it
            foreach ($element as $element_name=>$element_value) {
                // this is baked into mongodb, so doesn't really belong in a mapping file
                if ($element_name == '_id') {
                    $element['ir_project_sample_id'] = $element['_id'];
                    continue;
                }

                //apply mapping if it exists
                if (isset($repo_to_output[$element_name]) && ($repo_to_output[$element_name] != '')) {
                    $element[$repo_to_output[$element_name]] = $element_value;
                    unset($element[$element_name]);
                }
            }

            array_push($return_array, $element);
        }

        return $return_array;
    }

    public static function list($params)
    {
        $l = static::all();

        return $l;
    }
}
