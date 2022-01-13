<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class AirrRearrangement extends Model
{
    protected $collection;

    public function __construct()
    {
        if (isset($_ENV['DB_SEQUENCES_COLLECTION'])) {
            $this->collection = $_ENV['DB_SEQUENCES_COLLECTION'];
        } else {
            $this->collection = 'sequences';
        }
        //timeouts are set in seconds, so we should convert to miliseconds for
        //  mongoDB
        if (isset($_ENV['COUNT_QUERY_TIMEOUT'])) {
            $this->count_timeout = (int) $_ENV['COUNT_QUERY_TIMEOUT'] * 1000;
        } else {
            $this->count_timeout = 0;
        }
        if (isset($_ENV['FETCH_QUERY_TIMEOUT'])) {
            $this->fetch_timeout = (int) $_ENV['FETCH_QUERY_TIMEOUT'] * 1000;
        } else {
            $this->fetch_timeout = 0;
        }
        if (isset($_ENV['TEMP_FILE_FOLDER'])) {
            $this->temp_files = $_ENV['TEMP_FILE_FOLDER'];
        } else {
            $this->temp_files = sys_get_temp_dir();
        }
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getCountTimeout()
    {
        return $this->count_timeout;
    }

    public function getFetchTimeout()
    {
        return $this->fetch_timeout;
    }

    public function getTempFolder()
    {
        return $this->temp_files;
    }

    public $timestamps = false;
    protected $max_results = 25;

    public static function airrRearrangementSingle($rearrangement_id)
    {
        //function that finds a single rearrangement based on the provided $rearrangement_id
        $query = new self();
        $query = $query->where('_id', $rearrangement_id);
        $result = $query->get();

        return $result->toArray();
    }

    public static function airrRearrangementRequest($params)
    {
        //function that processes AIRR API request and returns an array of fields matching
        //   the filters, with optional start number and max number of results
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_to_repository = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        ini_set('memory_limit', '2G');
        set_time_limit(60 * 60 * 24);

        $query_string = '{}';

        $query_string = '{}';
        $options = [];

        $fields_to_retrieve = [];
        $query = new self();
        $options['projection'] = [];
        // if we have filters, process them
        if (isset($params['filters']) && $params['filters'] != '' && ! empty($params['filters'])) {
            $query_string = AirrUtils::processAirrFilter($params['filters'], $airr_names, $airr_types, $db_types);
            if ($query_string == null) {
                //something went wrong
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
                $required_fields = FileMapping::createMappingArray('ir_repository', $map_fields_column, ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
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
                return 'error';
            }
        }

        // if we have size parameter, don't take more than that number of results
        if (isset($params['size'])) {
            if (is_int($params['size'])) {
                $options['limit'] = abs($params['size']);
            } else {
                return 'error';
            }
        }

        //echo "<br/>\n Returning $query_string";
        //return ($query_string);

        //if facets is set we want to aggregate by that fields using the sum operation
        if (isset($params['facets']) && $params['facets'] != '') {
            if (! isset($airr_names[$params['facets']])) {
                return 'error';
            }
            $aggOptions = [];
            $aggOptions[0]['$match'] = json_decode(preg_replace('/\\\\/', '\\\\\\\\', $query_string));
            //$aggOptions[1]['$unwind'] = '$' . $airr_names[$params['facets']];
            $aggOptions[1]['$group'] = ['_id'=> [$airr_names[$params['facets']] => '$' . $airr_names[$params['facets']]]];
            $aggOptions[1]['$group']['count'] = ['$sum' => 1];
            $options['maxTimeMS'] = $query->getCountTimeout();
            $options['noCursorTimeout'] = true;

            $list = DB::collection($query->getCollection())->raw()->aggregate($aggOptions, $options);
        } else {
            $options['maxTimeMS'] = $query->getFetchTimeout();
            $options['noCursorTimeout'] = true;

            $list = DB::collection($query->getCollection())->raw()->find(json_decode(preg_replace('/\\\\/', '\\\\\\\\', $query_string), true), $options);
        }

        //return $list->toArray();
        return $list;
    }

    public static function airrRearrangementResponse($response_list, $response_type, $params)
    {
        //method that takes an array of AIRR terms and returns a JSON string
        //  that represents a repertoire response as defined in AIRR API
        ini_set('memory_limit', '2G');
        set_time_limit(60 * 60 * 24);

        //first, we need some mappings to convert database values to AIRR terms
        //  and bucket them into appropriate AIRR classes
        $db_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_names = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $repository_to_airr = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_query', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $db_to_service = FileMapping::createMappingArray('ir_repository', 'service_name', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_type = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_to_service_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'service_name', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);

        // rev_comp and functional field are sometimes stored with annotation values
        //  of + and 1 but AIRR standard requires them to be boolean. Scan the airr to service mapping
        //  for those two values here so we don't have to do it on every sequence.
        // For similar reason, we want a translation of ir_project_sample_id value, which connects
        //  rearrangement with repertoire
        $rev_comp_airr_name = $airr_names['rev_comp'];
        $functional_arr_name = $airr_names['functional'];
        $fields_to_display = [];

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
                $required_fields = FileMapping::createMappingArray('ir_adc_api_response', $map_fields_column, ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
                foreach ($required_fields as $name => $value) {
                    if ($value && strtolower($value) != 'false') {
                        $fully_qualified_path = $name;
                        $fields_to_display[$fully_qualified_path] = 1;
                    }
                }
            }
        }

        $first = true;
        // if neither required nor fields is set, we still want to return required
        if (! isset($params['include_fields']) && ! isset($params['fields'])) {
            $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'ir_rearrangement']]);
            foreach ($required_fields as $name => $value) {
                if ($value) {
                    $fully_qualified_path = $name;
                    $fields_to_display[$fully_qualified_path] = 1;
                }
            }
        }

        if ($response_type == 'json') {
            header('Content-Type: application/json; charset=utf-8');
            $response = AirrUtils::airrHeader();
            echo '{"Info":';
            echo json_encode($response['Info'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            echo ', "Rearrangement":[';
            echo "\n";
        }
        if ($response_type == 'tsv') {
            header('Content-Type: text/tsv; charset=utf-8');
            header('Content-Disposition: attachment;filename="data.tsv"');
        }
        //have to put commas between JSON elements, but not on the last one, so figure out if this is the first time through

        // if we have tsv, dump the return array's keys as headers
        if ($response_type == 'tsv') {
            echo implode(array_keys($fields_to_display), chr(9)) . "\n";
        }
        foreach ($response_list as $rearrangement) {
            $return_array = [];

            //null out the required fields, then populate from database.
            foreach ($fields_to_display as $display_field=>$value) {
                array_set($return_array, $display_field, null);
            }

            foreach ($rearrangement as $return_key => $return_element) {

                //make all the requested fields null before populating if there are results
                if (isset($repository_to_airr[$return_key]) && $repository_to_airr[$return_key] != '') {
                    $service_name = $db_to_service[$return_key];
                    if ($service_name == 'rev_comp') {
                        if ($return_element == '+') {
                            $return_element = false;
                        }
                        if ($return_element == '-') {
                            $return_element = true;
                        }
                    }
                    if ($service_name == 'functional') {
                        if ($return_element == 1) {
                            $return_element = true;
                        } elseif ($return_element == 0) {
                            $return_element = false;
                        }
                    }

                    //flatten any MongoDB ObjectId types
                    if (is_a($return_element, "MongoDB\BSON\ObjectId")) {
                        $return_element = $return_element->__toString();
                    }

                    if ($service_name == 'ir_project_sample_id') {
                        $return_element = (string) $return_element;
                    }

                    //in TSV we want our boolean values to be 'T' and 'F'
                    if ($airr_type[$repository_to_airr[$return_key]] == 'boolean' && $response_type == 'tsv') {
                        if (strtolower($return_element) == 'true' || $return_element == true) {
                            $return_element = 'T';
                        } else {
                            $return_element = 'F';
                        }
                    }

                    // mongodb BSON array needs to be serialized or it can't be used in TSV output
                    //  we also want to return a string, not an array, in JSON response
                    if ($return_element != null && is_a($return_element, "MongoDB\Model\BSONArray")) {
                        $return_element = implode($return_element->jsonSerialize(), ', or ');
                    }
                    array_set($return_array, $repository_to_airr[$return_key], $return_element);
                } else {
                    //problem with TSV download is that there are fields not in the database but it's hard to
                    //  put them into headers - for now skip them in the TSV

                    if ($response_type == 'tsv') {
                        continue;
                    }
                    //if there are fields not in AIRR standard but in database, we want to
                    //  send those along too, provided they don't override AIRR elements already mapped
                    // mongodb BSON array needs to be serialized or it can't be used in TSV output
                    //
                    if ($return_element != null && is_a($return_element, "MongoDB\Model\BSONArray")
                        && $response_type == 'tsv') {
                        $return_element = implode($return_element->jsonSerialize(), ', ');
                    }
                    if (! isset($return_array[$return_key])) {
                        $return_array[$return_key] = $return_element;
                    }
                }
            }

            if ($response_type == 'tsv') {
                echo implode($return_array, chr(9)) . "\n";
            } else {
                if ($first) {
                    $first = false;
                } else {
                    echo ',';
                }
                echo json_encode($return_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
        }
        if ($response_type == 'json') {
            echo "]}\n";
        }
    }

    public static function airrRearrangementFacetsResponse($response_list)
    {
        $return_array = [];
        $response_mapping = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_query', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        //MongoDB by default aggregates in the format _id: {column: value}, count: sum
        //  AIRR expects {column: value, count: sum} {column: value2, count: sum}
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

    public static function airrRearrangementResponseSingle($rearrangement)
    {

        //take a single rearrangement from database query and create a response as per
        //  AIRR API standard
        $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_to_service_mapping = FileMapping::createMappingArray('ir_adc_api_response', 'service_name', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_type = FileMapping::createMappingArray('ir_adc_api_response', 'airr_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);

        foreach ($required_fields as $name => $value) {
            if ($value) {
                $fully_qualified_path = $name;
                $fields_to_display[$fully_qualified_path] = 1;
            }
        }
        $return_list = [];
        $result = [];
        //make all the requested fields null before populating if there are results
        foreach ($fields_to_display as $display_field=>$value) {
            array_set($result, $display_field, null);
        }

        $response_mapping = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_response', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        foreach ($rearrangement as $key=>$value) {
            if (isset($response_mapping[$key]) && $response_mapping[$key] != '') {
                if (is_array($value)) {
                    $result[$response_mapping[$key]] = implode($value, ', or ');
                } else {
                    $result[$response_mapping[$key]] = $value;
                }
            } else {
                //if there are fields not in AIRR standard but in database, we want to
                //  send those along too, provided they don't override AIRR elements already mapped
                if (! isset($result[$key])) {
                    $result[$key] = $value;
                }
            }
        }
        $return_list[] = $result;

        return $return_list;
    }

    public static function airrOptimizedRearrangementRequest($request)
    {
        //method to run an optimized MongoDB query on the filters that can support it
        //  a single '=' search on an indexed field, a search on indexed field and
        //  repertoire id, or an aggregation on prior two cases on repertoire_id
        ini_set('memory_limit', '2G');
        set_time_limit(60 * 60 * 24);

        $service_to_airr_mapping = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $service_to_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $repertoire_service_to_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_to_repository_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $repertoire_airr_to_repository_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Repertoire', 'IR_Repertoire']]);
        $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $airr_to_service_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'service_name', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
        $repertoire_db_types = FileMapping::createMappingArray('ir_repository', 'ir_repository_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Repertoire', 'IR_Repertoire']]);

        $sample_id_list = [];
        $query_params = [];
        $db_filters = [];

        $query = new self();

        $filter = '';
        $facets = '';
        if (isset($request['filters']) && count($request['filters']) > 0) {
            $filter = $request['filters'];
        }
        if (isset($request['facets'])) {
            $facets = $request['facets'];
        }

        //if we have no filter except repertoire_id, and facets are on repertoire_id, we don't need to go to rearrangement at all
        // we can just pull the cached value from our repertoire collection
        if ($facets == $service_to_airr_mapping['ir_project_sample_id'] && ($filter == '' ||
            ($filter['op'] != 'and' && $filter['content']['field'] == $service_to_airr_mapping['ir_project_sample_id']))) {
            $sample_id_query = new AirrRepertoire();
            if ($filter != '') {
                if (is_array($filter['content']['value'])) {
                    $repertoire_id_list = [];
                    foreach ($filter['content']['value'] as $filter_id) {
                        $repertoire_id_list[] = AirrUtils::typeConvertHelperRaw($filter_id, $repertoire_db_types[$repertoire_service_to_db_mapping['ir_project_sample_id']]);
                    }
                    $sample_id_query = $sample_id_query->whereIn($repertoire_service_to_db_mapping['ir_project_sample_id'], $repertoire_id_list);
                } else {
                    $sample_id_query = $sample_id_query->where($repertoire_service_to_db_mapping['ir_project_sample_id'], '=', AirrUtils::typeConvertHelperRaw($filter['content']['value'], $repertoire_db_types[$repertoire_service_to_db_mapping['ir_project_sample_id']]));
                }
            }
            $result = $sample_id_query->get();

            foreach ($result as $repertoire) {
                if ($repertoire[$repertoire_service_to_db_mapping['ir_sequence_count']] > 0) {
                    $return['_id'][$service_to_db_mapping['ir_project_sample_id']] = (string) $repertoire[$repertoire_service_to_db_mapping['ir_project_sample_id']];
                    $return['count'] = $repertoire[$repertoire_service_to_db_mapping['ir_sequence_count']];
                    $sample_id_list[] = $return;
                }
            }
            header('Content-Type: application/json; charset=utf-8');

            $response = AirrUtils::airrHeader();
            $response['Facet'] = self::airrRearrangementFacetsResponse($sample_id_list);
            $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            echo $json;
        } else {
            //create a list of repertoire ids we'll be looping over, and a filter we can pass to MongoDB
            AirrUtils::optimizeRearrangementFilter($filter, $airr_to_repository_mapping, $airr_types, $service_to_airr_mapping, $service_to_db_mapping, $sample_id_list, $db_filters, $db_types);

            //if we don't have a list of repertoire ids, we will be looping over all the database entries
            if (count($sample_id_list) == 0) {
                $sample_id_query = new AirrRepertoire();
                $result = $sample_id_query->get();
                foreach ($result as $repertoire) {
                    $sample_id_list[] = $repertoire[$repertoire_service_to_db_mapping['ir_project_sample_id']];
                }
            }
            // if it's a facets query, we will have to do a count on repertoire_ids
            if ($facets == $service_to_airr_mapping['ir_project_sample_id']) {
                $return_list = [];

                $count_timeout = $query->getCountTimeout();
                $query_params['maxTimeMS'] = $count_timeout;

                foreach ($sample_id_list as $current_sample_id) {
                    $db_filters[$service_to_db_mapping['ir_project_sample_id']] = $current_sample_id;
                    $total = DB::collection($query->getCollection())->raw()->count($db_filters, $query_params);
                    if ($total > 0) {
                        $return['_id'][$service_to_db_mapping['ir_project_sample_id']] = (string) $current_sample_id;
                        $return['count'] = $total;
                        $return_list[] = $return;
                    }
                }
                header('Content-Type: application/json; charset=utf-8');

                $response = AirrUtils::airrHeader();
                $response['Facet'] = self::airrRearrangementFacetsResponse($return_list);
                $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                echo $json;
            } else {
                //it's a data query, either tsv or JSON, run it by repertoire_id and echo the results as a stream
                $start_at = 0;
                $max_values = 0;
                $projection_mapping = FileMapping::createMappingArray('ir_repository', 'projection');
                //check what kind of response we have, default to JSON
                $response_type = 'json';
                if (isset($request['format']) && $request['format'] != '') {
                    $response_type = strtolower($request['format']);
                }

                // rev_comp and functional field are sometimes stored with annotation values
                //  of + and 1 but AIRR standard requires them to be boolean. Scan the airr to service mapping
                //  for those two values here so we don't have to do it on every sequence.
                // For similar reason, we want a translation of ir_project_sample_id value, which connects
                //  rearrangement with repertoire
                $rev_comp_airr_name = $service_to_airr_mapping['rev_comp'];
                $functional_arr_name = $service_to_airr_mapping['functional'];

                //few other variables we use in other arrays, simply to avoid triple-nested array references
                // e.g. $psa_list[$sequence_list[$database_fields['ir_project_sample_id']]];
                $ir_project_sample_id_repository_name = $service_to_db_mapping['ir_project_sample_id'];

                // check if we have a start value or max value. with max, we stop sending data after that many results
                //  start is a bit iffier - we'll run our query and not output till we have seen that many results, but...
                //  this may not be consistent accross requests
                if (isset($request['size']) && intval($request['size']) > 0) {
                    $max_values = intval($request['size']);
                }
                if (isset($request['from']) && intval($request['from']) > 0) {
                    $start_at = intval($request['from']);
                }
                $fields_to_retrieve = [];
                $fields_to_display = [];
                $fetch_timeout = $query->getFetchTimeout();
                $query_params['maxTimeMS'] = $fetch_timeout;
                $query_params['noCursorTimeout'] = true;

                // if fields value is set, we will be using them in projection
                if (isset($request['fields']) && $request['fields'] != '') {
                    foreach ($request['fields'] as $airr_field_name) {
                        if (isset($airr_to_repository_mapping[$airr_field_name]) && $airr_to_repository_mapping[$airr_field_name] != '') {
                            $fields_to_retrieve[$airr_to_repository_mapping[$airr_field_name]] = 1;
                            $fields_to_display[$airr_field_name] = 1;
                        }
                    }
                    $query_params['projection'] = $fields_to_retrieve;
                }
                //if required fields are set, map the appropriate column to the return
                // if neither required nor fields is set, we still want to return required
                if (isset($request['include_fields'])) {
                    $map_fields_column = '';
                    switch ($request['include_fields']) {
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
                        $required_fields = FileMapping::createMappingArray('ir_adc_api_response', $map_fields_column, ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
                        foreach ($required_fields as $name => $value) {
                            if ($value && strtolower($value) != 'false') {
                                $fully_qualified_path = $name;
                                $fields_to_display[$fully_qualified_path] = 1;
                            }
                        }
                    }
                }

                // if neither required nor fields is set, we still want to return required
                if (! isset($request['include_fields']) && ! isset($request['fields'])) {
                    $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'ir_rearrangement']]);
                    foreach ($required_fields as $name => $value) {
                        if ($value) {
                            $fully_qualified_path = $name;
                            $fields_to_display[$fully_qualified_path] = 1;
                        }
                    }
                }

                $fields_to_display = array_keys($fields_to_display);
                $written_results = 0;
                if ($response_type == 'json') {
                    header('Content-Type: application/json; charset=utf-8');
                    $response = AirrUtils::airrHeader();
                    echo '{"Info":';
                    echo json_encode($response['Info'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

                    echo ', "Rearrangement":[';
                    echo "\n";
                }
                if ($response_type == 'tsv') {
                    //output the headers
                    echo implode($fields_to_display, chr(9)) . "\n";
                }
                $current_result = 0;
                $first = true;
                foreach ($sample_id_list as $current_sample_id) {
                    $db_filters[$service_to_db_mapping['ir_project_sample_id']] = $current_sample_id;
                    $result = DB::collection($query->getCollection())->raw()->find($db_filters, $query_params);
                    foreach ($result as $row) {
                        $sequence_list = $row;
                        $airr_list = [];

                        foreach ($airr_to_service_mapping as $airr_name => $service_name) {
                            if (isset($service_name) && isset($service_to_db_mapping[$service_name])) {
                                if (isset($sequence_list[$service_to_db_mapping[$service_name]])) {
                                    $airr_list[$airr_name] = $sequence_list[$service_to_db_mapping[$service_name]];
                                    if ($service_name == 'rev_comp') {
                                        if ($airr_list[$rev_comp_airr_name] == '+') {
                                            $airr_list[$rev_comp_airr_name] = false;
                                        }
                                        if ($airr_list[$rev_comp_airr_name] == '-') {
                                            $airr_list[$rev_comp_airr_name] = true;
                                        }
                                    }
                                    if ($service_name == 'functional') {
                                        if ($airr_list[$functional_arr_name] == 1) {
                                            $airr_list[$functional_arr_name] = true;
                                        } elseif ($airr_list[$functional_arr_name] == 0) {
                                            $airr_list[$functional_arr_name] = false;
                                        }
                                    }
                                    if ($service_name == 'ir_project_sample_id') {
                                        $airr_list[$airr_name] = (string) $airr_list[$airr_name];
                                    }
                                }
                            } else {
                                $airr_list[$airr_name] = null;
                            }
                        }

                        $current_result++;
                        $new_line = [];
                        foreach ($fields_to_display as $current_header) {
                            if (isset($airr_list[$current_header])) {
                                if (is_array($airr_list[$current_header])) {
                                    $new_line[$current_header] = implode($airr_list[$current_header], ', or');
                                } elseif ($airr_list[$current_header] != null && is_a($airr_list[$current_header], "MongoDB\Model\BSONArray")) {
                                    $new_line[$current_header] = implode($airr_list[$current_header]->jsonSerialize(), ', or ');
                                } else {
                                    //the database id should be converted to string using the BSON function
                                    if (is_a($airr_list[$current_header], "MongoDB\BSON\ObjectId")) {
                                        $airr_list[$current_header] = $airr_list[$current_header]->__toString();
                                    }
                                    $new_line[$current_header] = $airr_list[$current_header];
                                }
                            } else {
                                $new_line[$current_header] = null;
                            }

                            //in TSV we want our boolean values to be 'T' and 'F'
                            if (isset($new_line[$current_header]) && $airr_types[$current_header] == 'boolean' && $response_type == 'tsv') {
                                if (strtolower($new_line[$current_header]) == 'true' || $new_line[$current_header] == true) {
                                    $new_line[$current_header] = 'T';
                                } else {
                                    $new_line[$current_header] = 'F';
                                }
                            }
                        }
                        if ($current_result > $start_at) {
                            if ($response_type == 'tsv') {
                                echo implode($new_line, chr(9)) . "\n";
                            } else {
                                if ($first) {
                                    $first = false;
                                } else {
                                    echo ',';
                                }
                                echo json_encode($new_line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                            }
                            $written_results++;
                        }
                        if ($max_values > 0 && $written_results >= $max_values) {
                            break 2;
                        }
                    }
                }
                if ($response_type == 'json') {
                    echo "]}\n";
                }
            }
        }
    }
}
