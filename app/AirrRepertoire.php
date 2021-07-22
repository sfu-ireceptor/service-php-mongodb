<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class AirrRepertoire extends Model
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

    public static function findRepertoire($params)
    {
        //function that takes in an array of key->value pairs, searches the database with $and operator on them,
        //  and returns the result set

        $query = new self;
        //var_dump($params); die();
        $repository_names = FileMapping::createMappingArray('ir_repository', 'service_name', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        foreach ($params as $key=>$value) {
            if (isset($repository_names[$key])) {
                $query = $query->where($key, '=', $value);
            }
        }

        return $query->get()->toArray();
    }

    public static function airrRepertoireRequest($params)
    {
        //function that processes AIRR API request and returns a response
        //  currently the response is iReceptor API response
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);

        $query_string = '{}';
        $options = [];
        $options['projection'] = [];
        $fields_to_retrieve = [];
        $query = new self();
        // if we have filters, process them
        if (isset($params['filters']) && $params['filters'] != '' && ! empty($params['filters'])) {
            $query_string = AirrUtils::processAirrFilter($params['filters'], $airr_names, $airr_types, $db_types);
            if ($query_string == null) {
                return 'error';
            }
        }
        // if fields parameter is set, we only want to return the fields specified
        if (isset($params['fields']) && $params['fields'] != '') {
            foreach ($params['fields'] as $airr_field_name) {
                if (isset($airr_names[$airr_field_name]) && $airr_names[$airr_field_name] != '') {
                    $fields_to_retrieve[$airr_names[$airr_field_name]] = 1;
                }
            }
            $options['projection'] = $fields_to_retrieve;
        }
        //if required fields are set, map the appropriate column to the return
        // if neither required nor fields is set, we still want to return required
        if (isset($params['include_fields'])) {
            $map_fields_column = '';
            switch ($params['include_fields']) {
                case 'miairr':
                    $map_fields_column = 'airr_miairr';
                    break;
                case 'airr-core':
                    $map_fields_column = 'airr_required';
                    break;
                case 'airr-schema':
                    $map_fields_column = 'airr_spec';
                    break;
                default:
                    break;
            }

            if ($map_fields_column != '') {
                $required_fields = FileMapping::createMappingArray('ir_repository', $map_fields_column, ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
                foreach ($required_fields as $name => $value) {
                    if ($value && strtolower($value) != 'false') {
                        $fields_to_retrieve[$name] = 1;
                    }
                }
                $options['projection'] = array_merge($options['projection'], $fields_to_retrieve);
            }
        }

        // if we have from parameter, start the query at that value
        //  if it's not an int, fail
        if (isset($params['from'])) {
            if (is_int($params['from'])) {
                $options['skip'] = abs($params['from']);
            } else {
                return 'from_error';
            }
        }

        // if we have size parameter, don't take more than that number of results
        if (isset($params['size'])) {
            if (is_int($params['size'])) {
                $options['limit'] = abs($params['size']);
            } else {
                return 'size_error';
            }
        }
        //echo "<br/>\n Returning $query_string";die();
        //return ($query_string);
        //if facets is set we want to aggregate by that fields using the sum operation
        if (isset($params['facets']) && $params['facets'] != '') {
            if (! isset($airr_names[$params['facets']])) {
                return 'error';
            }
            $aggOptions = [];
            $aggOptions[0]['$match'] = json_decode(preg_replace('/\\\\/', '\\\\\\\\', $query_string));
            $aggOptions[1]['$group'] = ['_id'=> [$airr_names[$params['facets']] => '$' . $airr_names[$params['facets']]]];
            $aggOptions[1]['$group']['count'] = ['$sum' => 1];

            $list = DB::collection($query->getCollection())->raw()->aggregate($aggOptions);
        } else {
            $list = DB::collection($query->getCollection())->raw()->find(json_decode(preg_replace('/\\\\/', '\\\\\\\\', $query_string), true), $options);
        }

        return $list->toArray();
    }

    public static function airrRepertoireSingle($repertoire_id)
    {
        //return a single repertoire based on the repertoire_id
        $repository_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $repository_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);

        $query = new self;
        // we have to adjust query based on type of field, because it will default to string
        switch ($repository_types['repertoire_id']) {
            case 'integer':
                $repertoire_id = (int) $repertoire_id;
                break;
            case 'number':
                $repertoire_id = (float) $repertoire_id;
                break;
            case 'string':
                $repertoire_id = (string) $repertoire_id;
                break;
        }
        $query = $query->where($repository_names['repertoire_id'], '=', $repertoire_id);
        $result = $query->get();

        return $result->toArray();
    }

    public static function airrRepertoireResponse($response_list, $params)
    {
        //method that takes an array of AIRR terms and returns a JSON string
        //  that represents a repertoire response as defined in AIRR API

        //first, we need some mappings to convert database values to AIRR terms
        //  and bucket them into appropriate AIRR classes
        $airr_classes = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_response', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $db_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_names = FileMapping::createMappingArray('service_name', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_class_to_name = FileMapping::createMappingArray('ir_adc_api_query', 'ir_adc_api_response', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $repository_to_airr = FileMapping::createMappingArray('ir_repository', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $db_names_to_airr_types = FileMapping::createMappingArray('ir_repository', 'airr_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $fields_to_display = [];
        $airr_is_array = FileMapping::createMappingArray('ir_adc_api_response', 'airr_is_array', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $service_to_airr_response = FileMapping::createMappingArray('service_name', 'ir_adc_api_response', 'airr_is_array', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);

        // if fields parameter is set, we only want to return the fields specified
        if (isset($params['fields']) && $params['fields'] != '') {
            foreach ($params['fields'] as $airr_field_name) {
                if (isset($airr_class_to_name[$airr_field_name]) && $airr_class_to_name[$airr_field_name] != '') {
                    $fully_qualified_path = $airr_class_to_name[$airr_field_name];

                    $fields_to_display[$fully_qualified_path] = 1;
                }
            }
        }
        //if required fields are set, map the appropriate column to the return
        // if neither required nor fields is set, we still want to return required
        if (isset($params['include_fields'])) {
            $map_fields_column = '';
            switch ($params['include_fields']) {
                case 'miairr':
                    $map_fields_column = 'airr_miairr';
                    break;
                case 'airr-core':
                    $map_fields_column = 'airr_required';
                    break;
                case 'airr-schema':
                    $map_fields_column = 'airr_spec';
                    break;
                default:
                    break;
            }

            if ($map_fields_column != '') {
                $required_fields = FileMapping::createMappingArray('ir_adc_api_response', $map_fields_column, ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
                foreach ($required_fields as $name => $value) {
                    if ($value && strtolower($value) != 'false') {
                        $fully_qualified_path = $name;
                        $fields_to_display[$fully_qualified_path] = 1;
                    }
                }
            }
        }

        // if neither required nor fields is set, we still want to return required
        if (! isset($params['include_fields']) && ! isset($params['fields'])) {
            $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
            foreach ($required_fields as $name => $value) {
                if ($value) {
                    $fully_qualified_path = $name;
                    $fields_to_display[$fully_qualified_path] = 1;
                }
            }
        }
        //each iReceptor 'sample' is an AIRR repertoire consisting of a single sample and  a single rearrangement set
        //  associated with it, so we will take the array of samples and place each element into an appropriate section
        //  of AIRR reperotoire response
        $return_list = [];
        foreach ($response_list as $repertoire) {
            $return_array = [];

            //make all the requested fields null before populating if there are results
            foreach ($fields_to_display as $display_field=>$value) {
                array_set($return_array, $display_field, null);
            }

            foreach ($repertoire as $return_key => $return_element) {
                if (isset($airr_classes[$return_key]) && $airr_classes[$return_key] != '') {
                    $fully_qualified_path = $airr_classes[$return_key];

                    // typecast the return values
                    $return_value = $return_element;
                    if (isset($db_names_to_airr_types[$return_key])) {

                        //we only want to typecast values that are set, because
                        //   a 'null' is considered 0/unset in PHP so it converts it to
                        //	 appopriate value based on type
                        if (isset($return_value)) {
                            switch ($db_names_to_airr_types[$return_key]) {
                            // make sure that type actually matches value or fail
                            case 'integer':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('intval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('intval', array_map(AirrUtils::stringToNumber, $return_element));
                                } else {
                                    $return_value = intval(AirrUtils::stringToNumber($return_element));
                                }
                                break;
                            case 'number':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('doubleval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('doubleval', array_map(AirrUtils::stringToNumber, $return_element));
                                } else {
                                    $return_value = floatval(AirrUtils::stringToNumber($return_element));
                                }
                                break;
                            case 'boolean':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('boolval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('boolval', $return_element);
                                } else {
                                    $return_value = boolval($return_element);
                                }
                                break;
                            case 'string':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('strval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('strval', $return_element);
                                } else {
                                    $return_value = strval($return_element);
                                }
                                break;
                            case 'object':
                                if (is_object($return_value)) {
                                    $return_value = $return_element->jsonSerialize();
                                }
                                break;
                            default:
                                //bad data type
                                break;
                                }
                            // there's a chance a field that should be an array in response wasn't processed as one
                            //  in that case we want to convert it to array. Heuristic is that a string of data processing files
                            //  or a string of keywords might be a comma-separated list, otherwise just convert to array as-is
                            if (isset($airr_is_array[$fully_qualified_path]) && strtolower($airr_is_array[$fully_qualified_path]) != 'false'
                                 && boolval($airr_is_array[$fully_qualified_path]) && isset($return_value) && ! is_array($return_value)) {
                                if (in_array($fully_qualified_path, [$service_to_airr_response['data_processing_files'], $service_to_airr_response['keywords_study']])
                                    && is_string($return_value)) {
                                    $return_value = array_map('trim', explode(',', $return_value));
                                } else {
                                    $return_value = [$return_value];
                                }
                            }
                        }
                    }

                    array_set($return_array, $fully_qualified_path, $return_value);
                } else {
                    //if there are fields not in AIRR standard but in database, we want to
                    //  send those along too, provided they don't override AIRR elements already mapped
                    if (! isset($return_array[$return_key])) {
                        $return_array[$return_key] = $return_element;
                    }
                }
            }

            $return_list[] = $return_array;
        }

        return $return_list;
    }

    public static function airrRepertoireResponseSingle($response_list)
    {
        //method that takes an array of AIRR terms and returns a JSON string
        //  that represents a repertoire response as defined in AIRR API

        //first, we need some mappings to convert database values to AIRR terms
        //  and bucket them into appropriate AIRR classes
        $airr_classes = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_query', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $db_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_names = FileMapping::createMappingArray('service_name', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $repository_to_airr = FileMapping::createMappingArray('ir_repository', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $db_names_to_airr_types = FileMapping::createMappingArray('ir_repository', 'airr_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);

        //each iReceptor 'sample' is an AIRR repertoire consisting of a single sample and  a single rearrangement set
        //  associated with it, so we will take the array of samples and place each element into an appropriate section
        //  of AIRR reperotoire response

        $return_list = [];
        $fields_to_display = [];

        $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        foreach ($required_fields as $name => $value) {
            if ($value) {
                $fully_qualified_path = $name;
                $fields_to_display[$fully_qualified_path] = 1;
            }
        }

        foreach ($response_list as $repertoire) {
            $return_array = [];

            //make all the requested fields null before populating if there are results
            foreach ($fields_to_display as $display_field=>$value) {
                array_set($return_array, $display_field, null);
            }

            foreach ($repertoire as $return_key => $return_element) {
                if (isset($airr_classes[$return_key]) && $airr_classes[$return_key] != '') {
                    $fully_qualified_path = $airr_classes[$return_key];
                    //AIRR API defines 'sample' as an array. we only have one so we insert a 0 index after
                    //   the sample. If needed, we could keep a counter of samples and adjust it accordingly
                    $fully_qualified_path = preg_replace("/^sample\.pcr_target\./", 'sample.pcr_target.0.', $fully_qualified_path);
                    $fully_qualified_path = preg_replace("/^sample\./", 'sample.0.', $fully_qualified_path);

                    //likewise for data_processing
                    $fully_qualified_path = preg_replace("/^data_processing\./", 'data_processing.0.', $fully_qualified_path);

                    //likewise diagnosis
                    $fully_qualified_path = preg_replace("/^subject.diagnosis\./", 'subject.diagnosis.0.', $fully_qualified_path);
                    // typecast the return values
                    $return_value = $return_element;
                    if (isset($db_names_to_airr_types[$return_key])) {

                        //we only want to typecast values that are set, because
                        //   a 'null' is considered 0/unset in PHP so it converts it to
                        //   appopriate value based on type
                        if (isset($return_value)) {
                            switch ($db_names_to_airr_types[$return_key]) {
                            // make sure that type actually matches value or fail
                            case 'integer':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('intval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('intval', array_map(AirrUtils::stringToNumber, $return_element));
                                } else {
                                    $return_value = intval(AirrUtils::stringToNumber($return_element));
                                }
                                break;
                            case 'number':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('doubleval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('doubleval', array_map(AirrUtils::stringToNumber, $return_element));
                                } else {
                                    $return_value = floatval(AirrUtils::stringToNumber($return_element));
                                }
                                break;
                            case 'boolean':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('boolval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('boolval', $return_element);
                                } else {
                                    $return_value = boolval($return_element);
                                }
                                break;
                            case 'string':
                                if (is_object($return_value)) {
                                    //arrays and similar objects may be BSONArray and BSONObject, which must be
                                    //  serialized before PHP can handle them
                                    $return_value = array_map('strval', $return_element->jsonSerialize());
                                } elseif (is_array($return_value)) {
                                    $return_value = array_map('strval', $return_element);
                                } else {
                                    $return_value = strval($return_element);
                                }
                                break;
                            default:
                                //bad data type
                                break;
                                }
                            // there's a chance a field that should be an array in response wasn't processed as one
                            //  in that case we want to convert it to array. Heuristic is that a string of data processing files
                            //  or a string of keywords might be a comma-separated list, otherwise just convert to array as-is
                            if (isset($airr_is_array[$fully_qualified_path]) && strtolower($airr_is_array[$fully_qualified_path]) != 'false'
                                 && boolval($airr_is_array[$fully_qualified_path]) && isset($return_value) && ! is_array($return_value)) {
                                if (in_array($fully_qualified_path, [$service_to_airr_response['data_processing_files'], $service_to_airr_response['keywords_study']])
                                    && is_string($return_value)) {
                                    $return_value = array_map('trim', explode(',', $return_value));
                                } else {
                                    $return_value = [$return_value];
                                }
                            }
                        }
                    }
                    array_set($return_array, $fully_qualified_path, $return_value);
                } else {
                    //if there are fields not in AIRR standard but in database, we want to
                    //  send those along too, provided they don't override AIRR elements already mapped
                    if (! isset($return_array[$return_key])) {
                        $return_array[$return_key] = $return_element;
                    }
                }
            }

            $return_list[] = $return_array;
        }

        return $return_list;
    }

    public static function airrRepertoireFacetsResponse($response_list)
    {
        $return_array = [];
        $response_mapping = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_query', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);

        //MongoDB by default aggregates in the format _id: {column: value}, count: sum
        //  AIRR expects {column: value, count: sum} {column: value2, count: sum}
        //  This method fills the AIRR API response with values from MongoDB query
        foreach ($response_list as $response) {
            $temp = [];
            $facet = $response['_id'];
            $count = $response['count'];
            $facet_name = $response_mapping[key($facet)];
            $temp[$facet_name] = $facet[key($facet)];
            $temp['count'] = $count;
            $return_array[] = $temp;
        }

        return $return_array;
    }
}
