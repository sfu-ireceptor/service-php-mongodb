<?php

//class that contains various utility functions for AIRR API

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

class AirrUtils extends Model
{
    //method to convert a value to a given type and encode in a way
    //  suitable for JSON query
    public static function typeConvertHelper($value, $type)
    {
        if (! isset($type) || ! isset($value)) {
            return;
        }

        switch ($type) {
            case 'integer':
                if (is_array($value)) {
                    return json_encode(array_map('intval', $value));
                } else {
                    return intval($value);
                }
                break;
            case 'number':
                if (is_array($value)) {
                    return json_encode(array_map('floatval', $value));
                } else {
                    return floatval($value);
                }
                break;
            case 'string':
            case 'array':
                if (is_array($value)) {
                    return json_encode(array_map('strval', $value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    return  json_encode(strval($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                break;
            case 'boolean':
                if (is_array($value)) {
                    return json_encode(array_map('boolval', $value));
                } else {
                    return json_encode(boolval($value));
                }
                break;
            default:
                return;
                break;
        }
    }

    //check that parameters other than filter are set using proper format
    public static function verifyParameters($request)
    {
        $file_types = ['tsv', 'json'];
        $include_fields = ['airr-core', 'miairr', 'airr-schema'];

        //from has to be a positive integer
        if (isset($request['from'])) {
            if (! is_int($request['from']) || $request['from'] < 0) {
                return 'From parameter needs to be a positive integer.';
            }
        }

        //size has to be a positive integer
        if (isset($request['size'])) {
            if (! is_int($request['size']) || $request['size'] < 0) {
                return 'Size parameter needs to be a positive integer.';
            }
        }

        //include_fields should be one of the enumerated values
        if (isset($request['include_fields']) && ! in_array($request['include_fields'], $include_fields)) {
            return 'Included fields has to be a valid enumeration.';
        }

        //file type should be one of the enumerated values
        if (isset($request['format']) && ! in_array($request['format'], $file_types)) {
            return 'File type has to be tsv or json.';
        }
    }

    // php has some issues converting numbers that are actually formatted strings
    //  e.g. 153,242 or 4*10^06
    //  we can try making it so it's more suitable for type casts, but the correct way
    //  is to ensure it's done right in the database
    public static function stringToNumber($value)
    {
        //if we can't treat it as a string, return
        if (! is_string($value)) {
            return $value;
        }

        //strip out the commas - hopefully the database doesn't follow european conventions
        //anything of the form 14.2*10^14 should be replaced with E14 which intval and floatval can handle
        $return_value = preg_replace("/\,/", '', $value);
        $return_value = preg_replace("/\*10\^/", 'E', $return_value);

        return $return_value;
    }

    //method to convert a value to a given type and encode in a way
    //  suitable for raw query
    public static function typeConvertHelperRaw($value, $type)
    {
        if (! isset($type) || ! isset($value)) {
            return;
        }

        switch ($type) {
            case 'integer':
                if (is_array($value)) {
                    return array_map('intval', $value);
                } else {
                    return intval($value);
                }
                break;
            case 'number':
                if (is_array($value)) {
                    return array_map('floatval', $value);
                } else {
                    return floatval($value);
                }
                break;
            case 'string':
            case 'array':
                if (is_array($value)) {
                    return array_map('strval', $value);
                } else {
                    return strval($value);
                }
                break;
            case 'boolean':
                if (is_array($value)) {
                    return array_map('boolval', $value);
                } else {
                    return boolval($value);
                }
                break;
            default:
                return;
                break;
        }
    }

    public static function processAirrFilter($f, $airr_to_db_array, $airr_types_array, $db_types_array)
    {
        //method to process an AIRR API filter object
        //  based on design by Scott Christley
        if (! (isset($f['op'])) || $f['op'] == '') {
            return;
        }
        if (! (isset($f['content'])) || $f['content'] == '') {
            return;
        }
        $field = '';
        $type = '';
        $db_type = '';
        $content = $f['content'];
        $operator = $f['op'];

        if (isset($content['field']) && $content['field'] != '') {
            // fields are of form sample.subject.subject_id
            //   use the mapping from airr terms to repository terms to create queries
            if (isset($airr_to_db_array[$content['field']]) && $airr_to_db_array[$content['field']] != null
                && $airr_to_db_array[$content['field']] != '') {
                $field = $airr_to_db_array[$content['field']];
            } else {
                return;
            }

            // check if the field provided exists in the mapping file
            if (isset($airr_types_array[$content['field']])) {
                $type = $airr_types_array[$content['field']];
                $db_type = $db_types_array[$content['field']];
            } else {
                return;
            }
        }

        if ($type == '' && $field != '') {
            Log::error("Type not found $field");
        }

        if (isset($content['value'])) {
            switch ($type) {
                // make sure that type actually matches value or fail
                case 'integer':
                    if (is_array($content['value'])) {
                        foreach ($content['value'] as $array_member) {
                            if (! is_int($array_member)) {
                                return;
                            }
                        }
                        $value = self::typeConvertHelper($content['value'], $db_type);
                    } else {
                        if (is_int($content['value'])) {
                            $value = self::typeConvertHelper($content['value'], $db_type);
                        } else {
                            return;
                        }
                    }
                    break;
                case 'number':
                    if (is_array($content['value'])) {
                        foreach ($content['value'] as $array_member) {
                            if (! (is_int($array_member) || is_float($array_member))) {
                                return;
                            }
                        }
                        $value = self::typeConvertHelper($content['value'], $db_type);
                    } else {
                        if (is_float($content['value']) || is_int($content['value'])) {
                            $value = self::typeConvertHelper($content['value'], $db_type);
                        } else {
                            return;
                        }
                    }
                    break;
                case 'boolean':
                    if (is_array($content['value'])) {
                        foreach ($content['value'] as $array_member) {
                            if (! is_bool($array_member)) {
                                return;
                            }
                        }
                        $value = self::typeConvertHelper($content['value'], $db_type);
                    } else {
                        if (is_bool($content['value'])) {
                            $value = self::typeConvertHelper($content['value'], $db_type);
                        } else {
                            return;
                        }
                    }
                    break;
                case 'string':
                case 'array':
                    if (is_array($content['value'])) {
                        foreach ($content['value'] as $array_member) {
                            if (! is_string($array_member)) {
                                return;
                            }
                        }
                        $value = self::typeConvertHelper($content['value'], $db_type);
                    } else {
                        if (is_string($content['value'])) {
                            $value = self::typeConvertHelper($content['value'], $db_type);
                        } else {
                            return;
                        }
                    }
                    break;
                default:
                    //bad data type
                    return;
                    break;
                }

            //check also that 'in' and 'exlcude' ops have array parameter, and all
            //  others do not
            // 'and' and 'or' can go either ways so ignore them
            switch ($f['op']) {
                    case 'and':
                    case 'or':
                        break;
                    case 'in':
                    case 'exclude':
                        if (! (is_array($content['value']))) {
                            return;
                        }
                        break;
                    default:
                        if (is_array($content['value'])) {
                            return;
                        }
                        break;
                }
        }
        switch ($f['op']) {
            case '=':
                if (isset($field) && $field != '' && isset($value)) {
                    return '{"' . $field . '":' . $value . '}';
                } else {
                    return;
                }
                break;
            case '!=':
                if (isset($field) && $field != '' && isset($value)) {
                    return '{"' . $field . '":{"$ne":' . $value . '}}';
                } else {
                    return;
                }
                break;
            case '<':
                if (isset($field) && $field != '' && isset($value)) {
                    return '{"' . $field . '":{"$lt":' . $value . '}}';
                } else {
                    return;
                }
                break;
            case '>':
                if (isset($field) && $field != '' && isset($value)) {
                    return '{"' . $field . '":{"$gt":' . $value . '}}';
                } else {
                    return;
                }
                break;
            case '<=':
                if (isset($field) && $field != '' && isset($value)) {
                    return '{"' . $field . '":{"$lte":' . $value . '}}';
                } else {
                    return;
                }
            case '>=':
                if (isset($field) && $field != '' && isset($value)) {
                    return '{"' . $field . '":{"$gte":' . $value . '}}';
                } else {
                    return;
                }
                break;
            case 'contains':
                if (isset($field) && $field != '' && isset($value) && $type == 'string') {
                    //sometimes, we might have a non-string in database being used in contains query
                    //  we should convert it to json string
                    if (! is_string($value)) {
                        $value = json_encode(strval($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    return '{"' . $field . '":{"$regex":' . preg_quote($value, '/') . ',"$options":"i"}}';
                } else {
                    return;
                }
                break;
            case 'is':
            case 'is missing':
                if (isset($field) && $field != '') {
                    return '{"' . $field . '":{"$exists":false}}';
                } else {
                    return;
                }
                break;
            case 'not':
            case 'is not missing':
                if (isset($field) && $field != '') {
                    return '{"' . $field . '":{"$exists":true}}';
                } else {
                    return;
                }
                break;
            case 'in':
                if (isset($field) && $field != '' && isset($value) && is_array(json_decode($value))) {
                    return '{"' . $field . '":{"$in":' . $value . '}}';
                } else {
                    return;
                }
                break;
            case 'exclude':
                if (isset($field) && $field != '' && isset($value) && is_array(json_decode($value))) {
                    return '{"' . $field . '":{"$nin":' . $value . '}}';
                } else {
                    return;
                }
                break;
            case 'and':
                if (is_array($content) && count($content) > 1) {
                    $exp_list = [];
                    foreach ($content as $content_chunk) {
                        $exp = self::processAirrFilter($content_chunk, $airr_to_db_array, $airr_types_array, $db_types_array);
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
                break;
            case 'or':
                if (is_array($content) && count($content) > 1) {
                    $exp_list = [];
                    foreach ($content as $content_chunk) {
                        $exp = self::processAirrFilter($content_chunk, $airr_to_db_array, $airr_types_array, $db_types_array);
                        if (isset($exp)) {
                            array_push($exp_list, $exp);
                        } else {
                            return;
                        }
                    }

                    return '{"$or":[' . implode(',', $exp_list) . ']}';
                } else {
                    return;
                }
                break;
            default:
                Log::error('Unknown op');

                return;
                break;
        } //end switch ($op)

        // should not get here
    }

    public static function queryOptimizable($query)
    {
        //method to check if a rearrangement query can be optimized for iReceptor repository
        //  returns true if yes, false otherwise
        //rules are:
        //  -if it's an equals query on a single indexed field, or single indexed field and repertoire id, yes
        //  -if it's a facets query on repertoire_id, and equals on an indexed field, yes
        //  -if it's a contains query on junction_aa, and optionally repertoire_id, yes
        //  -if it's a facets query on repertoire_id with no filter, yes
        //  -otherwise, not optimizable

        //create helper mappings to avoid hard-coding terms
        //  TODO? - add 'is_indexed' column to the mapping file, in case we adjust indexes

        //return false;

        try {
            $airr_names = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
            // array of indexed fields - as usual, hard-coded terms are in 'service_name' column of the mapping file
            //  note that indexed fields on non-AIRR terms can and do exist
            $indexed_fields = ([$airr_names['ir_project_sample_id'], $airr_names['junction_aa_length'],
                $airr_names['junction_aa'], $airr_names['v_call'], $airr_names['d_call'],
                $airr_names['j_call'],
                $airr_names['functional'],
                $airr_names['vgene_gene'], $airr_names['vgene_family'],
                $airr_names['dgene_gene'], $airr_names['dgene_family'],
                $airr_names['jgene_gene'], $airr_names['jgene_family'], ]
            );
            $filters = '';
            $facets = '';

            //size must be an integer
            if (isset($query['size']) && ! is_int($query['size'])) {
                return false;
            }

            // similar to size, from must be integer
            if (isset($query['from']) && ! is_int($query['from'])) {
                return false;
            }

            if (isset($query['filters'])) {
                $filters = $query['filters'];
            }
            if (isset($query['facets'])) {
                $facets = $query['facets'];
            } else {
                //for now, let's only optimize facets queries. the count() vs aggregate() is about a
                //  factor of 10 in performance, whereas downloading tsv/json data would do index scan
                //  either way
                //return false;
            }
            // no filters, no facets - doesn't matter, so go through the regular pipeline
            if (($filters == '' || count($filters) == 0) && $facets == '') {
                //echo 'no filter';

                return false;
            }

            //check that the filter is correct - easiest way is to run it through unoptimized
            //  filter creation and see if it's returning null
            $airr_db_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
            $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
            $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Rearrangement', 'IR_Rearrangement']]);
            $query_string = self::processAirrFilter($filters, $airr_db_names, $airr_types, $db_types);
            if ($query_string == null && $filters != '') {
                return false;
            }

            //first pass is easiest, any facets query not on repertoire_id will not be optimized
            if ($facets != '' && $facets != 'repertoire_id') {
                //echo 'bad facet ' . $facets;

                return false;
            }

            //if we have no filter, it's a count on repertoire_id and we can definitely optimize it
            if ($filters == '' || count($filters) == 0) {
                return true;
            }

            //if filter is not 'and', '=', 'contains' or 'in', we can't do it
            if (! in_array($filters['op'], ['and', '=', 'contains', 'in'])) {
                //echo 'bad op ' . $filters['op'];

                return false;
            }
            //single '=' query on indexed fields, definitely optimizable (if facets exist they should be on repertoire_id at this point
            //  so no reason to check).
            if ($filters['op'] == '=' && in_array($filters['content']['field'], $indexed_fields)) {
                return true;
            }

            //Special case - contains query on junction_aa field translates into a 'substring' query and is thus optimizable
            if ($filters['op'] == 'contains' && $filters['content']['field'] == $airr_names['junction_aa']) {
                return true;
            }

            //a 'in' query on repertoire_id is optimizable, we just will iterate over it
            if ($filters['op'] == 'in' && $filters['content']['field'] == $airr_names['ir_project_sample_id']) {
                return true;
            }

            //most complicated case is an 'and' filter with two parameters, an indexed field with '=' query and repertoire_id '=' or 'contains'
            if ($filters['op'] == 'and' && is_array($filters['content']) && count($filters['content']) == 2) {
                $has_indexed = false;
                foreach ($filters['content'] as $filter) {
                    //first, check if op is '=', 'in' or 'contains'. Anything else we can't do
                    if ($filter['op'] != '=' && $filter['op'] !== 'contains' & $filter['op'] != 'in') {
                        // echo 'bad op ' . $filter['op'];

                        return false;
                    }

                    //can't do anything good if field isn't indexed
                    if (! in_array($filter['content']['field'], $indexed_fields, true)) {
                        //echo 'unindex field ' . $filter['content']['field'];

                        return false;
                    }

                    //we can only do 'contains' on junction_aa
                    if ($filter['op'] == 'contains' && $filter['content']['field'] != $airr_names['junction_aa']) {
                        //echo 'bad contains ' . $filter['content']['field'];

                        return false;
                    }

                    //'in' only works on repertoire_id
                    if ($filter['op'] == 'in' && $filter['content']['field'] != $airr_names['ir_project_sample_id']) {
                        //echo 'bad in on ' . $filter['content']['field'];

                        return false;
                    }

                    //'=' works on any indexed field - BUT - we have to make sure query only uses one
                    //  indexed field and repertoir_id
                    if ($filter['op'] == '=') {
                        //special case right now is junction_aa where we optimized on substring search, not exact match
                        if ($filter['content']['field'] == $airr_names['junction_aa']) {
                            return false;
                        }
                        if ($has_indexed) {
                            //echo 'Attempt to AND multiple fields ' . var_dump($filters);

                            return false;
                        } else {
                            if (in_array($filter['content']['field'], $indexed_fields) && $filter['content']['field'] != $airr_names['ir_project_sample_id']) {
                                $has_indexed = true;
                            }
                        }
                    }
                }

                return true;
            }
            //any filter with more than two parameters can't be optimized
            if (is_array($filters['content']) && count($filters['content']) > 2) {
                return false;
            }
            // shouldn't get here
            //echo 'no return';

            return false;
        } catch (\Exception $e) {
            echo "$e";

            return false;
        }
    }

    //if given a filter, map it to appropriate database field, create a MongoDB query,
    //  separate repertoire ids (if any) into a list and return it for further processing
    public static function optimizeRearrangementFilter($filter, $airr_to_repository_mapping, $airr_types, $service_to_airr_mapping, $service_to_db_mapping, &$sample_id_list, &$db_filters, $db_types_array)
    {
        // if our top-level op is 'and', that means we have a list of repertoire_ids and another query parameter
        //   (otherwise, the query would not be optimizable)
        if ($filter['op'] == 'and') {
            foreach ($filter['content'] as $filter_piece) {
                // repertoire query goes into sample_id_list
                if ($filter_piece['content']['field'] == $service_to_airr_mapping['ir_project_sample_id']) {
                    if (is_array($filter_piece['content']['value'])) {
                        //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                        $empty_array = true;
                        foreach ($filter_piece['content']['value'] as $filter_id) {
                            $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter_piece['content']['field']]);
                            $empty_array = false;
                        }
                        if ($empty_array) {
                            $sample_id_list[] = null;
                        }
                    } else {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                    }
                } else {
                    // if we have junction_aa, we do a query on substring field instead, case insensitive
                    if ($airr_to_repository_mapping[$filter_piece['content']['field']] == $service_to_airr_mapping['junction_aa']
                            && $filter_piece['op'] == 'contains') {
                        $db_filters[$service_to_db_mapping['substring']] = strtoupper((string) $filter_piece['content']['value']);
                    } else {
                        $db_filters[$airr_to_repository_mapping[$filter_piece['content']['field']]] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                    }
                }
            }
        } else {
            //we have a single query parameter, either repertoire id or filter
            if ($filter['content']['field'] == $service_to_airr_mapping['ir_project_sample_id']) {
                if (is_array($filter['content']['value'])) {
                    //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                    $empty_array = true;
                    foreach ($filter['content']['value'] as $filter_id) {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter['content']['field']]);
                        $empty_array = false;
                    }
                    if ($empty_array) {
                        $sample_id_list[] = null;
                    }
                } else {
                    $sample_id_list[] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
                }
            } else {
                // if we have junction_aa, we do a query on substring field instead
                if ($airr_to_repository_mapping[$filter['content']['field']] == $service_to_airr_mapping['junction_aa']
                    && $filter['op'] == 'contains') {
                    $db_filters[$service_to_db_mapping['substring']] = strtoupper((string) $filter['content']['value']);
                } else {
                    $db_filters[$airr_to_repository_mapping[$filter['content']['field']]] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
                }
            }
        }
    }

    public static function airrHeader($type = 'AIRRC', $optimized = false)
    {
        $response = [];

        $optimized_string = '';
        if ($optimized) {
            $optimized_string = 'Optimized ';
        }
        $response['Info'] = Info::getAirrInfo();

        return $response;
    }

    public static function convertDbToAirr($result_list, $db_to_airr_mapping, $db_to_service_mapping,
            $airr_type, $fields_to_display, $response_type, $fields_requested = false)
    {
        //given a single repository query result and mapping fields, process it
        //  into an array suitable for display
        foreach ($result_list as $return_key => $return_element) {

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
            if (isset($db_to_airr_mapping[$return_key]) && $db_to_airr_mapping[$return_key] != '') {
                $service_name = $db_to_service_mapping[$return_key];
                if ($service_name == 'ir_annotation_set_metadata_id_expression') {
                    $return_element = (string) $return_element;
                }
                //in TSV we want our boolean values to be 'T' and 'F'
                if ($airr_type[$db_to_airr_mapping[$return_key]] == 'boolean' && $response_type == 'tsv') {
                    if (strtolower($return_element) == 'true' || $return_element == true) {
                        $return_element = 'T';
                    } else {
                        $return_element = 'F';
                    }
                }
                array_set($return_array, $db_to_airr_mapping[$return_key], $return_element);
            } else {
                //if there are fields not in AIRR standard but in database, we want to
                //  send those along too, but only if there was no constraint on the fields
                if (! isset($fields_to_display[$return_key]) && $response_type != 'tsv' &&
                    $return_key != '_id' && ! $fields_requested) {
                    $return_array[$return_key] = $return_element;
                }
            }
        }

        return $return_array;
    }

    public static function cloneQueryOptimizable($query)
    {
        //method to check if a clone query can be optimized for iReceptor repository
        //  returns true if yes, false otherwise
        //rules are:
        //  -if it's an equals query on a single indexed field, or single indexed field and repertoire id, yes
        //  -if it's a facets query on repertoire_id, and equals on an indexed field, yes
        //  -if it's a contains query on junction_aa, and optionally repertoire_id, yes
        //  -if it's a facets query on repertoire_id with no filter, yes
        //  -otherwise, not optimizable

        //create helper mappings to avoid hard-coding terms
        //  TODO? - add 'is_indexed' column to the mapping file, in case we adjust indexes

        //return false;

        try {
            $airr_names = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['clone', 'ir_clone', 'Clone', 'IR_Clone']]);
            // array of indexed fields - as usual, hard-coded terms are in 'service_name' column of the mapping file
            //  note that indexed fields on non-AIRR terms can and do exist
            $indexed_fields = ([$airr_names['repertoire_id'], $airr_names['junction_aa_length'],
                $airr_names['junction_aa'], $airr_names['v_call'], $airr_names['d_call'],
                $airr_names['j_call'], ]
            );
            $filters = '';
            $facets = '';

            //size must be an integer
            if (isset($query['size']) && ! is_int($query['size'])) {
                return false;
            }

            // similar to size, from must be integer
            if (isset($query['from']) && ! is_int($query['from'])) {
                return false;
            }

            if (isset($query['filters'])) {
                $filters = $query['filters'];
            }
            if (isset($query['facets'])) {
                $facets = $query['facets'];
            } else {
                //for now, let's only optimize facets queries. the count() vs aggregate() is about a
                //  factor of 10 in performance, whereas downloading tsv/json data would do index scan
                //  either way
                //return false;
            }
            // no filters, no facets - doesn't matter, so go through the regular pipeline
            if (($filters == '' || count($filters) == 0) && $facets == '') {
                //echo 'no filter';

                return false;
            }

            //check that the filter is correct - easiest way is to run it through unoptimized
            //  filter creation and see if it's returning null
            $airr_db_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['clone', 'ir_clone', 'Clone', 'IR_Clone']]);
            $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['clone', 'ir_clone', 'Clone', 'IR_Clone']]);
            $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['clone', 'ir_clone', 'Clone', 'IR_Clone']]);
            $query_string = self::processAirrFilter($filters, $airr_db_names, $airr_types, $db_types);
            if ($query_string == null && $filters != '') {
                return false;
            }

            //first pass is easiest, any facets query not on repertoire_id will not be optimized
            if ($facets != '' && $facets != 'repertoire_id') {
                //echo 'bad facet ' . $facets;

                return false;
            }

            //if we have no filter, it's a count on repertoire_id and we can definitely optimize it
            if ($filters == '' || count($filters) == 0) {
                return true;
            }

            //if filter is not 'and', '=', 'contains' or 'in', we can't do it
            if (! in_array($filters['op'], ['and', '=', 'contains', 'in'])) {
                //echo 'bad op ' . $filters['op'];

                return false;
            }
            //single '=' query on indexed fields, definitely optimizable (if facets exist they should be on repertoire_id at this point
            //  so no reason to check).
            //But, junction_aa is special. Right now it's not really indexed, so we want to skip it on '=' but allow on 'contains'
            if ($filters['op'] == '=' && in_array($filters['content']['field'], $indexed_fields) && $filters['content']['field'] != $airr_names['junction_aa']) {
                return true;
            }
            //Special case - contains query on junction_aa field translates into a 'substring' query and is thus optimizable
            if ($filters['op'] == 'contains' && $filters['content']['field'] == $airr_names['junction_aa']) {
                return true;
            }

            //a 'in' query on repertoire_id is optimizable, we just will iterate over it
            if ($filters['op'] == 'in' && $filters['content']['field'] == $airr_names['repertoire_id']) {
                return true;
            }

            //most complicated case is an 'and' filter with two parameters, an indexed field with '=' query and repertoire_id '=' or 'contains'
            if ($filters['op'] == 'and' && is_array($filters['content']) && count($filters['content']) == 2) {
                $has_indexed = false;
                foreach ($filters['content'] as $filter) {
                    //first, check if op is '=', 'in' or 'contains'. Anything else we can't do
                    if ($filter['op'] != '=' && $filter['op'] !== 'contains' & $filter['op'] != 'in') {
                        // echo 'bad op ' . $filter['op'];

                        return false;
                    }

                    //can't do anything good if field isn't indexed
                    if (! in_array($filter['content']['field'], $indexed_fields, true)) {
                        //echo 'unindex field ' . $filter['content']['field'];

                        return false;
                    }

                    //we can only do 'contains' on junction_aa
                    if ($filter['op'] == 'contains' && $filter['content']['field'] != $airr_names['junction_aa']) {
                        //echo 'bad contains ' . $filter['content']['field'];

                        return false;
                    }

                    //'in' only works on repertoire_id
                    if ($filter['op'] == 'in' && $filter['content']['field'] != $airr_names['repertoire_id']) {
                        //echo 'bad in on ' . $filter['content']['field'];

                        return false;
                    }

                    //'=' works on any indexed field - BUT - we have to make sure query only uses one
                    //  indexed field and repertoir_id
                    if ($filter['op'] == '=') {
                        //special case right now is junction_aa where we optimized on substring search, not exact match
                        if ($filter['content']['field'] == $airr_names['junction_aa']) {
                            return false;
                        }
                        if ($has_indexed) {
                            //echo 'Attempt to AND multiple fields ' . var_dump($filters);

                            return false;
                        } else {
                            if (in_array($filter['content']['field'], $indexed_fields) && $filter['content']['field'] != $airr_names['repertoire_id']) {
                                $has_indexed = true;
                            }
                        }
                    }
                }

                return true;
            }
            //any filter with more than two parameters can't be optimized
            if (is_array($filters['content']) && count($filters['content']) > 2) {
                return false;
            }
            // shouldn't get here
            //echo 'no return';

            return false;
        } catch (\Exception $e) {
            echo "$e";

            return false;
        }
    }

    //if given a filter, map it to appropriate database field, create a MongoDB query,
    //  separate repertoire ids (if any) into a list and return it for further processing
    public static function optimizeCloneFilter($filter, $airr_to_repository_mapping, $airr_types, $service_to_airr_mapping, $service_to_db_mapping, &$sample_id_list, &$db_filters, $db_types_array)
    {
        // if our top-level op is 'and', that means we have a list of repertoire_ids and another query parameter
        //   (otherwise, the query would not be optimizable)
        if ($filter['op'] == 'and') {
            foreach ($filter['content'] as $filter_piece) {
                // repertoire query goes into sample_id_list
                if ($filter_piece['content']['field'] == $service_to_airr_mapping['repertoire_id']) {
                    if (is_array($filter_piece['content']['value'])) {
                        //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                        $empty_array = true;
                        foreach ($filter_piece['content']['value'] as $filter_id) {
                            $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter_piece['content']['field']]);
                            $empty_array = false;
                        }
                        if ($empty_array) {
                            $sample_id_list[] = null;
                        }
                    } else {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                    }
                } else {
                    // if we have junction_aa, we do a query on substring field instead
                    if ($airr_to_repository_mapping[$filter_piece['content']['field']] == $service_to_airr_mapping['junction_aa']) {
                        $db_filters[$service_to_db_mapping['substring']] = (string) $filter_piece['content']['value'];
                    } else {
                        $db_filters[$airr_to_repository_mapping[$filter_piece['content']['field']]] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                    }
                }
            }
        } else {
            //we have a single query parameter, either repertoire id or filter
            if ($filter['content']['field'] == $service_to_airr_mapping['repertoire_id']) {
                if (is_array($filter['content']['value'])) {
                    //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                    $empty_array = true;
                    foreach ($filter['content']['value'] as $filter_id) {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter['content']['field']]);
                        $empty_array = false;
                    }
                    if ($empty_array) {
                        $sample_id_list[] = null;
                    }
                } else {
                    $sample_id_list[] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
                }
            } else {
                // if we have junction_aa, we do a query on substring field instead
                if ($airr_to_repository_mapping[$filter['content']['field']] == $service_to_airr_mapping['junction_aa']) {
                    $db_filters[$service_to_db_mapping['substring']] = (string) $filter['content']['value'];
                } else {
                    $db_filters[$airr_to_repository_mapping[$filter['content']['field']]] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
                }
            }
        }
    }

    public static function cellQueryOptimizable($query)
    {
        //determine if a gene expression query is optimizable
        // first pass-always fail
        //return  false;
        //method to check if a cell query can be optimized for iReceptor repository
        //  returns true if yes, false otherwise
        //rules are:
        //  -if it's an equals query on a single indexed field, or single indexed field and repertoire id, yes
        //  -if it's a facets query on repertoire_id, and equals on an indexed field, yes
        //  -if it's a facets query on repertoire_id with no filter, yes
        //  -otherwise, not optimizable

        //create helper mappings to avoid hard-coding terms
        //  TODO? - add 'is_indexed' column to the mapping file, in case we adjust indexes

        try {
            $airr_names = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['cell', 'ir_cell', 'Cell', 'IR_Cell']]);
            // array of indexed fields - as usual, hard-coded terms are in 'service_name' column of the mapping file
            //  note that indexed fields on non-AIRR terms can and do exist
            $indexed_fields = ([$airr_names['repertoire_id'], $airr_names['data_processing_id'],
                $airr_names['cell_id'], $airr_names['expression_study_method'], $airr_names['virtual_pairing'],
            ]
            );
            $filters = '';
            $facets = '';

            //size must be an integer
            if (isset($query['size']) && ! is_int($query['size'])) {
                return false;
            }

            // similar to size, from must be integer
            if (isset($query['from']) && ! is_int($query['from'])) {
                return false;
            }

            if (isset($query['filters'])) {
                $filters = $query['filters'];
            }
            if (isset($query['facets'])) {
                $facets = $query['facets'];
            }
            // no filters, no facets - doesn't matter, so go through the regular pipeline
            if (($filters == '' || count($filters) == 0) && $facets == '') {
                //echo 'no filter';

                return false;
            }

            //check that the filter is correct - easiest way is to run it through unoptimized
            //  filter creation and see if it's returning null
            $airr_db_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['cell', 'ir_cell', 'Cell', 'IR_Cell']]);
            $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['cell', 'ir_cell', 'Cell', 'IR_Cell']]);
            $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['cell', 'ir_cell', 'Cell', 'IR_Cell']]);
            $query_string = self::processAirrFilter($filters, $airr_db_names, $airr_types, $db_types);
            if ($query_string == null && $filters != '') {
                return false;
            }

            //first pass is easiest, any facets query not on repertoire_id will not be optimized
            if ($facets != '' && $facets != 'repertoire_id') {
                //echo 'bad facet ' . $facets;

                return false;
            }

            //if we have no filter, it's a count on repertoire_id and we can definitely optimize it
            if ($filters == '' || count($filters) == 0) {
                return true;
            }

            //if filter is not 'and', '=',  or 'in', we can't do it
            if (! in_array($filters['op'], ['and', '=', 'in'])) {
                //echo 'bad op ' . $filters['op'];

                return false;
            }
            //single '=' query on indexed fields, definitely optimizable (if facets exist they should be on repertoire_id at this point
            //  so no reason to check).
            if ($filters['op'] == '=' && in_array($filters['content']['field'], $indexed_fields)) {
                return true;
            }
            //a 'in' query on repertoire_id is optimizable, we just will iterate over it
            if ($filters['op'] == 'in' && $filters['content']['field'] == $airr_names['repertoire_id']) {
                return true;
            }

            //most complicated case is an 'and' filter with two parameters, an indexed field with '=' query and repertoire_id '=' or 'contains'
            if ($filters['op'] == 'and' && is_array($filters['content']) && count($filters['content']) == 2) {
                $has_indexed = false;
                foreach ($filters['content'] as $filter) {
                    //first, check if op is '=', 'in' or 'contains'. Anything else we can't do
                    if ($filter['op'] != '=' && $filter['op'] != 'in') {
                        // echo 'bad op ' . $filter['op'];

                        return false;
                    }

                    //can't do anything good if field isn't indexed
                    if (! in_array($filter['content']['field'], $indexed_fields, true)) {
                        //echo 'unindex field ' . $filter['content']['field'];

                        return false;
                    }

                    //'in' only works on repertoire_id
                    if ($filter['op'] == 'in' && $filter['content']['field'] != $airr_names['repertoire_id']) {
                        //echo 'bad in on ' . $filter['content']['field'];

                        return false;
                    }

                    //'=' works on any indexed field - BUT - we have to make sure query only uses one
                    //  indexed field and repertoir_id
                    if ($filter['op'] == '=') {
                        if ($has_indexed) {
                            //echo 'Attempt to AND multiple fields ' . var_dump($filters);

                            return false;
                        } else {
                            if (in_array($filter['content']['field'], $indexed_fields) && $filter['content']['field'] != $airr_names['repertoire_id']) {
                                $has_indexed = true;
                            }
                        }
                    }
                }

                return true;
            }
            //any filter with more than two parameters can't be optimized
            if (is_array($filters['content']) && count($filters['content']) > 2) {
                return false;
            }
            // shouldn't get here
            //echo 'no return';

            return false;
        } catch (\Exception $e) {
            echo "$e";

            return false;
        }
    }

    //if given a filter, map it to appropriate database field, create a MongoDB query,
    //  separate repertoire ids (if any) into a list and return it for further processing
    public static function optimizeCellFilter($filter, $airr_to_repository_mapping, $airr_types, $service_to_airr_mapping, $service_to_db_mapping, &$sample_id_list, &$db_filters, $db_types_array)
    {
        // if our top-level op is 'and', that means we have a list of repertoire_ids and another query parameter
        //   (otherwise, the query would not be optimizable)
        if ($filter['op'] == 'and') {
            foreach ($filter['content'] as $filter_piece) {
                // repertoire query goes into sample_id_list
                if ($filter_piece['content']['field'] == $service_to_airr_mapping['repertoire_id']) {
                    if (is_array($filter_piece['content']['value'])) {
                        //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                        $empty_array = true;
                        foreach ($filter_piece['content']['value'] as $filter_id) {
                            $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter_piece['content']['field']]);
                            $empty_array = false;
                        }
                        if ($empty_array) {
                            $sample_id_list[] = null;
                        }
                    } else {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                    }
                } else {
                    $db_filters[$airr_to_repository_mapping[$filter_piece['content']['field']]] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                }
            }
        } else {
            //we have a single query parameter, either repertoire id or filter
            if ($filter['content']['field'] == $service_to_airr_mapping['repertoire_id']) {
                if (is_array($filter['content']['value'])) {
                    //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                    $empty_array = true;
                    foreach ($filter['content']['value'] as $filter_id) {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter['content']['field']]);
                        $empty_array = false;
                    }
                    if ($empty_array) {
                        $sample_id_list[] = null;
                    }
                } else {
                    $sample_id_list[] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
                }
            } else {
                $db_filters[$airr_to_repository_mapping[$filter['content']['field']]] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
            }
        }
    }

    public static function geneExpressionQueryOptimizable($query)
    {
        //determine if a cell query is optimizable
        // first pass-always fail
        //return  false;
        //method to check if a cell query can be optimized for iReceptor repository
        //  returns true if yes, false otherwise
        //rules are:
        //  -if it's an equals query on a single indexed field, or single indexed field and repertoire id, yes
        //  -if it's a facets query on repertoire_id, and equals on an indexed field, yes
        //  -if it's a facets query on repertoire_id with no filter, yes
        //  -otherwise, not optimizable

        //create helper mappings to avoid hard-coding terms
        //  TODO? - add 'is_indexed' column to the mapping file, in case we adjust indexes

        try {
            $airr_names = FileMapping::createMappingArray('service_name', 'ir_adc_api_query', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
            // array of indexed fields - as usual, hard-coded terms are in 'service_name' column of the mapping file
            //  note that indexed fields on non-AIRR terms can and do exist
            $indexed_fields = ([$airr_names['repertoire_id'], $airr_names['data_processing_id'],
                $airr_names['cell_id'], $airr_names['property_id'], $airr_names['property'], $airr_names['value'],
            ]
            );
            $filters = '';
            $facets = '';

            //size must be an integer
            if (isset($query['size']) && ! is_int($query['size'])) {
                return false;
            }

            // similar to size, from must be integer
            if (isset($query['from']) && ! is_int($query['from'])) {
                return false;
            }

            if (isset($query['filters'])) {
                $filters = $query['filters'];
            }
            if (isset($query['facets'])) {
                $facets = $query['facets'];
            }
            // no filters, no facets - doesn't matter, so go through the regular pipeline
            if (($filters == '' || count($filters) == 0) && $facets == '') {
                //echo 'no filter';

                return false;
            }

            //check that the filter is correct - easiest way is to run it through unoptimized
            //  filter creation and see if it's returning null
            $airr_db_names = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
            $airr_types = FileMapping::createMappingArray('ir_adc_api_query', 'airr_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
            $db_types = FileMapping::createMappingArray('ir_adc_api_query', 'ir_repository_type', ['ir_class'=>['geneexpression', 'ir_expression', 'GeneExpression', 'IR_Expression']]);
            $query_string = self::processAirrFilter($filters, $airr_db_names, $airr_types, $db_types);
            if ($query_string == null && $filters != '') {
                return false;
            }

            //first pass is easiest, any facets query not on repertoire_id will not be optimized
            if ($facets != '' && $facets != 'repertoire_id') {
                //echo 'bad facet ' . $facets;

                return false;
            }

            //if we have no filter, it's a count on repertoire_id and we can definitely optimize it
            if ($filters == '' || count($filters) == 0) {
                return true;
            }

            //if filter is not 'and', '=' or 'in', we can't do it
            if (! in_array($filters['op'], ['and', '=', 'in'])) {
                //echo 'bad op ' . $filters['op'];

                return false;
            }
            //single '=' query on indexed fields, definitely optimizable (if facets exist they should be on repertoire_id at this point
            //  so no reason to check).
            if ($filters['op'] == '=' && in_array($filters['content']['field'], $indexed_fields)) {
                return true;
            }
            //a 'in' query on repertoire_id is optimizable, we just will iterate over it
            if ($filters['op'] == 'in' && $filters['content']['field'] == $airr_names['repertoire_id']) {
                return true;
            }

            //most complicated case is an 'and' filter with two parameters, an indexed field with '=' query and repertoire_id '=' or 'contains'
            if ($filters['op'] == 'and' && is_array($filters['content']) && count($filters['content']) == 2) {
                $has_indexed = false;
                foreach ($filters['content'] as $filter) {
                    //first, check if op is '=', 'in' or 'contains'. Anything else we can't do
                    if ($filter['op'] != '=' && $filter['op'] != 'in') {
                        // echo 'bad op ' . $filter['op'];

                        return false;
                    }

                    //can't do anything good if field isn't indexed
                    if (! in_array($filter['content']['field'], $indexed_fields, true)) {
                        //echo 'unindex field ' . $filter['content']['field'];

                        return false;
                    }

                    //'in' only works on repertoire_id
                    if ($filter['op'] == 'in' && $filter['content']['field'] != $airr_names['repertoire_id']) {
                        //echo 'bad in on ' . $filter['content']['field'];

                        return false;
                    }

                    //'=' works on any indexed field - BUT - we have to make sure query only uses one
                    //  indexed field and repertoir_id
                    if ($filter['op'] == '=') {
                        if ($has_indexed) {
                            //echo 'Attempt to AND multiple fields ' . var_dump($filters);

                            return false;
                        } else {
                            if (in_array($filter['content']['field'], $indexed_fields) && $filter['content']['field'] != $airr_names['repertoire_id']) {
                                $has_indexed = true;
                            }
                        }
                    }
                }

                return true;
            }
            //any filter with more than two parameters can't be optimized
            if (is_array($filters['content']) && count($filters['content']) > 2) {
                return false;
            }
            // shouldn't get here
            //echo 'no return';

            return false;
        } catch (\Exception $e) {
            echo "$e";

            return false;
        }
    }

    //if given a filter, map it to appropriate database field, create a MongoDB query,
    //  separate repertoire ids (if any) into a list and return it for further processing
    public static function optimizeGeneExpressionFilter($filter, $airr_to_repository_mapping, $airr_types, $service_to_airr_mapping, $service_to_db_mapping, &$sample_id_list, &$db_filters, $db_types_array)
    {
        // if our top-level op is 'and', that means we have a list of repertoire_ids and another query parameter
        //   (otherwise, the query would not be optimizable)
        if ($filter['op'] == 'and') {
            foreach ($filter['content'] as $filter_piece) {
                // repertoire query goes into sample_id_list
                if ($filter_piece['content']['field'] == $service_to_airr_mapping['repertoire_id']) {
                    if (is_array($filter_piece['content']['value'])) {
                        //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                        $empty_array = true;
                        foreach ($filter_piece['content']['value'] as $filter_id) {
                            $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter_piece['content']['field']]);
                            $empty_array = false;
                        }
                        if ($empty_array) {
                            $sample_id_list[] = null;
                        }
                    } else {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                    }
                } else {
                    $db_filters[$airr_to_repository_mapping[$filter_piece['content']['field']]] = self::typeConvertHelperRaw($filter_piece['content']['value'], $db_types_array[$filter_piece['content']['field']]);
                }
            }
        } else {
            //we have a single query parameter, either repertoire id or filter
            if ($filter['content']['field'] == $service_to_airr_mapping['repertoire_id']) {
                if (is_array($filter['content']['value'])) {
                    //corner case where an empty repertoire_id array is sent needs to fail, rather than take all repertoires
                    $empty_array = true;
                    foreach ($filter['content']['value'] as $filter_id) {
                        $sample_id_list[] = self::typeConvertHelperRaw($filter_id, $db_types_array[$filter['content']['field']]);
                        $empty_array = false;
                    }
                    if ($empty_array) {
                        $sample_id_list[] = null;
                    }
                } else {
                    $sample_id_list[] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
                }
            } else {
                $db_filters[$airr_to_repository_mapping[$filter['content']['field']]] = self::typeConvertHelperRaw($filter['content']['value'], $db_types_array[$filter['content']['field']]);
            }
        }
    }
}
