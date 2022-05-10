<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class AirrGeneExpression extends Model
{
    protected $collection;

    public function __construct()
    {
        if (isset($_ENV['DB_GENE_EXPRESSION_COLLECTION'])) {
            $this->collection = $_ENV['DB_GENE_EXPRESSION_COLLECTION'];
        } else {
            $this->collection = 'expression';
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

    public static function airrGeneExpressionSingle($expression_id)
    {
        //function that finds a single gene expression based on the provided $expression_id
        $query = new self();
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $db_expression_id_name = $repository_names['expression_id'];
        $query = $query->where($db_expression_id_name, $expression_id);
        $result = $query->get();

        return $result->toArray();
    }

    public static function airrGeneExpressionRequest($params)
    {
        //function that processes AIRR API request and returns an array of fields matching
        //   the filters, with optional start number and max number of results
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_to_repository = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
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
                $required_fields = FileMapping::createMappingArray('ir_repository', $map_fields_column, ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
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

        return $list;
    }

    public static function airrGeneExpressionFacetsResponse($response_list)
    {
        $return_array = [];
        $response_mapping = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_query', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
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

    public static function airrGeneExpressionResponse($response_list, $response_type, $params)
    {
        //method that takes an array of AIRR terms and returns a JSON string
        //  that represents a repertoire response as defined in AIRR API
        ini_set('memory_limit', '2G');
        set_time_limit(60 * 60 * 24);

        //first, we need some mappings to convert database values to AIRR terms
        //  and bucket them into appropriate AIRR classes
        $db_names = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_names = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $repository_to_airr = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_query', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $db_to_service = FileMapping::createMappingArray('ir_repository', 'service_name', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_type = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_to_service_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'service_name', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);

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
                $required_fields = FileMapping::createMappingArray('ir_adc_api_response', $map_fields_column, ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
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
            $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
            foreach ($required_fields as $name => $value) {
                if ($value) {
                    $fully_qualified_path = $name;
                    $fields_to_display[$fully_qualified_path] = 1;
                }
            }
        }

        if ($response_type == 'json') {
            header('Content-Type: application/json; charset=utf-8');
            $response = AirrUtils::AirrHeader('Cell Expression', false);
            echo '{"Info":';
            echo json_encode($response['Info'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            echo ', "CellExpression":[';
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
        foreach ($response_list as $expression) {
            $return_array = [];

            //null out the required fields, then populate from database.
            foreach ($fields_to_display as $display_field=>$value) {
                array_set($return_array, $display_field, null);
            }

            foreach ($expression as $return_key => $return_element) {

                //flatten any MongoDB ObjectId types
                if (is_a($return_element, "MongoDB\BSON\ObjectId")) {
                    $return_element = $return_element->__toString();
                }

                // mongodb BSON array needs to be serialized or it can't be used in TSV output
                //  we also want to return a string, not an array, in JSON response
                if ($return_element != null && is_a($return_element, "MongoDB\Model\BSONArray")) {
                    $return_element = implode($return_element->jsonSerialize(), ', or ');
                }

                //make all the requested fields null before populating if there are results
                if (isset($repository_to_airr[$return_key]) && $repository_to_airr[$return_key] != '') {
                    $service_name = $db_to_service[$return_key];
                    if ($service_name == 'ir_annotation_set_metadata_id_expression') {
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

                    array_set($return_array, $repository_to_airr[$return_key], $return_element);
                } else {
                    //if there are fields not in AIRR standard but in database, we want to
                    //  send those along too, but only if there was no constraint on the fields
                    if (! isset($params['include_fields']) && ! isset($params['fields']) &&
                            $response_type == 'json' && $return_key != '_id') {
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

    public static function airrGeneExpressionResponseSingle($expression)
    {

        //take a single gene expression from database query and create a response as per
        //  AIRR API standard
        $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_to_service_mapping = FileMapping::createMappingArray('ir_adc_api_response', 'service_name', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_type = FileMapping::createMappingArray('ir_adc_api_response', 'airr_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);

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

        $response_mapping = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_response', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        foreach ($expression as $key=>$value) {
            if (isset($response_mapping[$key]) && $response_mapping[$key] != '') {
                if (is_array($value)) {
                    $result[$response_mapping[$key]] = implode($value, ', or ');
                } else {
                    $result[$response_mapping[$key]] = $value;
                }
            } else {
                //if there are fields not in AIRR standard but in database, we want to
                //  send those along too
                $result[$key] = $value;
            }
        }
        $return_list[] = $result;

        return $return_list;
    }

    public static function airrOptimizedGeneExpressionRequest($request)
    {
        //method to run an optimized MongoDB query on the filters that can support it
        //  a single '=' search on an indexed field, a search on indexed field and
        //  repertoire id, or an aggregation on prior two cases on repertoire_id
        ini_set('memory_limit', '2G');
        set_time_limit(60 * 60 * 24);

        $service_to_airr_mapping = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $service_to_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $db_to_service_mapping = FileMapping::createMappingArray('ir_repository', 'service_name', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $db_to_airr_mapping = FileMapping::createMappingArray('ir_repository', 'ir_adc_api_response', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $repertoire_service_to_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_to_repository_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $repertoire_airr_to_repository_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $airr_to_service_mapping = FileMapping::createMappingArray('ir_adc_api_query', 'service_name', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
        $repertoire_db_types = FileMapping::createMappingArray('ir_repository', 'ir_repository_type', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $airr_type = FileMapping::createMappingArray('ir_adc_api_response', 'airr_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);

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

        //create a list of repertoire ids we'll be looping over, and a filter we can pass to MongoDB
        if (isset($filter) && $filter != '') {
            AirrUtils::optimizeGeneExpressionFilter($filter, $airr_to_repository_mapping, $airr_types, $service_to_airr_mapping, $service_to_db_mapping, $sample_id_list, $db_filters, $db_types);
        }
        //if we don't have a list of repertoire ids, we will be looping over all the database entries
        //if we do have it, loop through to retrieve the connector id
        $sample_id_query = new AirrRepertoire();
        $sample_id_query_results_list = [];
        if (count($sample_id_list) != 0) {
            $sample_id_query = $sample_id_query->whereIn($repertoire_service_to_db_mapping['ir_project_sample_id'], $sample_id_list);
        }
        $result = $sample_id_query->get();
        foreach ($result as $repertoire) {
            $current_repertoire_id = $repertoire[$repertoire_service_to_db_mapping['ir_project_sample_id']];
            if (! isset($sample_id_query_results_list[$current_repertoire_id])) {
                $sample_id_query_results_list[$current_repertoire_id] = [];
            }
            array_push($sample_id_query_results_list[$current_repertoire_id], $repertoire[$repertoire_service_to_db_mapping['ir_annotation_set_metadata_id']]);
        }
        // if it's a facets query, we will have to do a count on repertoire_ids
        if ($facets == $service_to_airr_mapping['repertoire_id']) {
            $return_list = [];

            $count_timeout = $query->getCountTimeout();
            $query_params['maxTimeMS'] = $count_timeout;

            foreach ($sample_id_query_results_list as $current_repertoire_id =>$current_sample_id) {
                $total = 0;
                foreach ($current_sample_id as $current_ir_annotation_set_metadata_id) {
                    $db_filters[$service_to_db_mapping['ir_annotation_set_metadata_id_expression']] = $current_ir_annotation_set_metadata_id;
                    $total += DB::collection($query->getCollection())->raw()->count($db_filters, $query_params);
                }
                if ($total > 0) {
                    $return['_id'][$service_to_db_mapping['repertoire_id']] = (string) $current_repertoire_id;
                    $return['count'] = $total;
                    $return_list[] = $return;
                }
            }

            header('Content-Type: application/json; charset=utf-8');
            $response = AirrUtils::airrHeader('Cell Expression', true);
            $response['Facet'] = self::airrGeneExpressionFacetsResponse($return_list);
            $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            echo $json;
        } else {
            //it's a data query, either tsv or JSON, run it by repertoire_id and echo the results as a stream
            $start_at = 0;
            $max_values = 0;
            //check what kind of response we have, default to JSON
            $response_type = 'json';
            if (isset($request['format']) && $request['format'] != '') {
                $response_type = strtolower($request['format']);
            }

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
                    $required_fields = FileMapping::createMappingArray('ir_adc_api_response', $map_fields_column, ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
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
                $required_fields = FileMapping::createMappingArray('ir_adc_api_response', 'airr_required', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
                foreach ($required_fields as $name => $value) {
                    if ($value) {
                        $fully_qualified_path = $name;
                        $fields_to_display[$fully_qualified_path] = 1;
                    }
                }
            }

            $header_fields = array_keys($fields_to_display);
            $written_results = 0;
            if ($response_type == 'json') {
                header('Content-Type: application/json; charset=utf-8');
                $response = AirrUtils::AirrHeader('Cell Expression', true);
                echo '{"Info":';
                echo json_encode($response['Info'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                echo ', "CellExpression":[';
                echo "\n";
            }
            if ($response_type == 'tsv') {
                //output the headers
                echo implode($header_fields, chr(9)) . "\n";
            }
            $current_result = 0;
            $first = true;
            foreach ($sample_id_query_results_list as $current_repertoire_id => $current_sample_id) {
                foreach ($current_sample_id as $current_ir_annotation_set_metadata_id) {
                    $db_filters[$service_to_db_mapping['ir_annotation_set_metadata_id_expression']] = $current_ir_annotation_set_metadata_id;
                    $result = DB::collection($query->getCollection())->raw()->find($db_filters, $query_params);
                    foreach ($result as $row) {
                        $expression_list = $row;
                        $return_array = [];

                        //null out the required fields, then populate from database.
                        foreach ($fields_to_display as $display_field=>$value) {
                            array_set($return_array, $display_field, null);
                        }
                        $return_array = AirrUtils::convertDbToAirr($expression_list, $db_to_airr_mapping, $db_to_service_mapping, $airr_type, $fields_to_display, $response_type, isset($request['include_fields']));

                        $current_result++;
                        if ($current_result > $start_at) {
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
                            $written_results++;
                        }
                        if ($max_values > 0 && $written_results >= $max_values) {
                            break 2;
                        }
                    }
                }
            }
            if ($response_type == 'json') {
                echo "]}\n";
            }
        }
    }
}
