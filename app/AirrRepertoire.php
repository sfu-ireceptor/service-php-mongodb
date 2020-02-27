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

    public static function airrRepertoireRequest($params)
    {
        //function that processes AIRR API request and returns a response
        //  currently the response is iReceptor API response
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_to_repository = FileMapping::createMappingArray('airr', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);

        $query_string = '{}';
        $options = [];
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
                if (isset($airr_to_repository[$airr_field_name]) && $airr_to_repository[$airr_field_name] != '') {
                    $fields_to_retrieve[$airr_to_repository[$airr_field_name]] = 1;
                }
            }
            $options['projection'] = $fields_to_retrieve;
        }
        //if required parameters is true, add them to the projection
        if (isset($params['include_required']) && $params['include_required'] == true) {
            $required_from_database = [];
            $required_fields = FileMapping::createMappingArray('ir_repository', 'airr_required', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
            foreach ($required_fields as $name => $value) {
                if ($value) {
                    $required_from_database[$name] = 1;
                }
            }
            $options['projection'] = array_merge($options['projection'], $required_from_database);
        }
        // if we have from parameter, start the query at that value
        if (isset($params['from']) && is_int($params['from'])) {
            $options['skip'] = abs($params['from']);
        }
        // if we have size parameter, don't take more than that number of results
        if (isset($params['size']) && is_int($params['size'])) {
            $options['limit'] = abs($params['size']);
        }

        //echo "<br/>\n Returning $query_string";die();
        //return ($query_string);

        //if facets is set we want to aggregate by that fields using the sum operation
        if (isset($params['facets']) && $params['facets'] != '') {
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
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $query = new self;
        $query = $query->where('_id', '=', (int) $repertoire_id);
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
		$airr_class_to_name = FileMapping::createMappingArray('ir_adc_api_query', 'ir_adc_api_response', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]) ;
        $repository_to_airr = FileMapping::createMappingArray('ir_repository', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $db_names_to_airr_types = FileMapping::createMappingArray('ir_repository', 'airr_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $fields_to_display=[];



        // if fields parameter is set, we only want to return the fields specified
        if (isset($params['fields']) && $params['fields'] != '') {
            foreach ($params['fields'] as $airr_field_name) {
            	if (isset($airr_class_to_name[$airr_field_name]) && $airr_class_to_name[$airr_field_name] !='')
            	{
            	    $fully_qualified_path = $airr_class_to_name[$airr_field_name];

                    //AIRR API defines 'sample' as an array. we only have one so we insert a 0 index after
                    //   the sample. If needed, we could keep a counter of samples and adjust it accordingly
                    $fully_qualified_path = preg_replace("/^sample\.pcr_target\./", 'sample.pcr_target.0.', $fully_qualified_path);
                    $fully_qualified_path = preg_replace("/^sample\./", 'sample.0.', $fully_qualified_path);

                    //likewise for data_processing
                    $fully_qualified_path = preg_replace("/^data_processing\./", 'data_processing.0.', $fully_qualified_path);

                    //likewise diagnosis
                    $fully_qualified_path = preg_replace("/^subject.diagnosis\./", 'subject.diagnosis.0.', $fully_qualified_path);

                    $fields_to_display[$fully_qualified_path] = 1;
                }
               }
        }
        //if required parameters is true, add them to the return
        if (isset($params['include_required']) && $params['include_required'] == true) {
           // $required_fields = FileMapping::createMappingArray('ir_adc_api_query', 'airr_required', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        	$required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
            foreach ($required_fields as $name => $value) {
                if ($value) {
                	$fully_qualified_path = $name;
/*
                    //AIRR API defines 'sample' as an array. we only have one so we insert a 0 index after
                    //   the sample. If needed, we could keep a counter of samples and adjust it accordingly
                    $fully_qualified_path = preg_replace("/^sample\.pcr_target\./", 'sample.pcr_target.0.', $fully_qualified_path);
                    $fully_qualified_path = preg_replace("/^sample\./", 'sample.0.', $fully_qualified_path);

                    //likewise for data_processing
                    $fully_qualified_path = preg_replace("/^data_processing\./", 'data_processing.0.', $fully_qualified_path);

                    //likewise diagnosis
                    $fully_qualified_path = preg_replace("/^subject.diagnosis\./", 'subject.diagnosis.0.', $fully_qualified_path);
*/
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
            foreach($fields_to_display as $display_field=>$value)
            {
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
                                if (is_array($return_element)) {
                                    $return_value = array_map('intval', $return_element);
                                } else {
                                    $return_value = (int) $return_element;
                                }
                                break;
                            case 'number':
                                if (is_array($return_element)) {
                                    $return_value = array_map('floatval', $return_element);
                                } else {
                                    $return_value = (float) $return_element;
                                }
                                break;
                            case 'boolean':
                                if (is_array($return_element)) {
                                    $return_value = array_map('boolval', $content['value']);
                                } else {
                                    $return_value = (bool) $return_element;
                                }
                                break;
                            case 'string':
                                if (is_array($return_element)) {
                                    $return_value = array_map('strval', $content['value']);
                                } else {
                                    $return_value = (string) $return_element;
                                }
                                break;
                            default:
                                //bad data type
                                break;
                                }
                        }
                    }
                    array_set($return_array, $fully_qualified_path, $return_value);
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

        //each iReceptor 'sample' is an AIRR repertoire consisting of a single sample and  a single rearrangement set
        //  associated with it, so we will take the array of samples and place each element into an appropriate section
        //  of AIRR reperotoire response

        $return_list = [];
        foreach ($response_list as $repertoire) {
            $return_array = [];

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

                    array_set($return_array, $fully_qualified_path, $return_element);
                }
            }

            $return_list[] = $return_array;
        }

        return $return_list;
    }

    public static function airrRepertoireFacetsResponse($response_list)
    {
        $return_array = [];
        $response_mapping = FileMapping::createMappingArray('ir_repository', 'airr', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);

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
