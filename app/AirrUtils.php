<?php

//class that contains various utility functions for AIRR API

namespace App;

use Log;
use Illuminate\Database\Eloquent\Model;

class AirrUtils extends Model
{
    public static function processAirrFilter($f, $airr_to_service_array, $airr_types_array)
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

        $content = $f['content'];
        $operator = $f['op'];

        if (isset($content['field']) && $content['field'] != '') {
            // fields are of form sample.subject.subject_id
            //   use the mapping from airr terms to repository terms to create queries
            if (isset($airr_to_service_array[$content['field']]) && $airr_to_service_array[$content['field']] != null
                && $airr_to_service_array[$content['field']] != '') {
                $field = $airr_to_service_array[$content['field']];
            } else {
                return;
            }

            // check if the field provided exists in the mapping file
            if (isset($airr_types_array[$content['field']])) {
                $type = $airr_types_array[$content['field']];
            } else {
                return;
            }
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
                    // special case: repertoire_id is string in API but int
                    //  in iReceptor database
                    if (is_array($content['value'])) {
                        if ($content['field'] == 'repertoire_id') {
                            $value = json_encode(array_map('intval', $content['value']));
                        } else {
                            $value = json_encode($content['value']);
                        }
                    } else {
                        if ($content['field'] == 'repertoire_id') {
                            $value = '"' . (int) $content['value'] . '"';
                        } else {
                            $value = '"' . $content['value'] . '"';
                        }
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
                    return '{"' . $field . '":{"$ne":' . $value . '}}';
                } else {
                    return;
                }
            case '<':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$lt":' . $value . '}}';
                } else {
                    return;
                }
            case '>':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$gt":' . $value . '}}';
                } else {
                    return;
                }
            case '<=':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$lte":' . $value . '}}';
                } else {
                    return;
                }
            case '>=':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$gte":' . $value . '}}';
                } else {
                    return;
                }
            case 'contains':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$regex":' . $value . ',"$options":"i"}}';
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
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$in":' . $value . '}}';
                } else {
                    return;
                }
            case 'exclude':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$nin":' . $value . '}}';
                } else {
                    return;
                }
            case 'and':
                if (is_array($content) && count($content) > 1) {
                    $exp_list = [];
                    foreach ($content as $content_chunk) {
                        $exp = self::processAirrFilter($content_chunk, $airr_to_service_array, $airr_types_array);
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
                        $exp = self::processAirrFilter($content_chunk, $airr_to_service_array, $airr_types_array);
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

        try {
            $airr_names = FileMapping::createMappingArray('service_name', 'airr', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);

            // array of indexed fields - as usual, hard-coded terms are in 'service_name' column of the mapping file
            //  note that indexed fields on non-AIRR terms can and do exist
            $indexed_fields = ([$airr_names['ir_project_sample_id'], $airr_names['junction_aa_length'],
            $airr_names['junction_aa'], $airr_names['v_call'], $airr_names['d_call'], $airr_names['j_call'],
            $airr_names['functional'], $airr_names['ir_annotation_tool'], ]);
            $filters = '';
            $facets = '';
            if (isset($query['filters'])) {
                $filters = $query['filters'];
            }

            if (isset($query['facets'])) {
                $facets = $query['facets'];
            }
            // no filters, no facets - doesn't matter, so go through the regular pipeline
            if (($filters == '' || count($filters) == 0) && $facets == '') {
                echo 'no filter';

                return false;
            }

            //first pass is easiest, any facets query not on repertoire_id will not be optimized
            if ($facets != '' && $facets != 'repertoire_id') {
                echo 'bad facet ' . $facets;

                return false;
            }

            //if we have no filter, it's a count on repertoire_id and we can definitely optimize it
            if ($filters == '' || count($filters) == 0) {
                return true;
            }

            //if filter is not 'and', '=', 'contains' or 'in', we can't do it
            if (! in_array($filters['op'], ['and', '=', 'contains', 'in'])) {
                echo 'bad op ' . $filters['op'];

                return false;
            }
            //single '=' query on indexed fields, definitely optimizable (if facets exist they should be on repertoire_id at this point
            //  so no reason to check)
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
            if ($filters['op'] == 'and' && is_array($filters['content']) && count($filters['content'] == 2)) {
                $has_indexed = false;
                foreach ($filters['content'] as $filter) {
                    //first, check if op is '=', 'in' or 'contains'. Anything else we can't do
                    if ($filter['op'] != '=' && $filter['op'] !== 'contains' & $filter['op'] != 'in') {
                        echo 'bad op ' . $filter['op'];

                        return false;
                    }

                    //can't do anything good if field isn't indexed
                    if (! in_array($filter['content']['field'], $indexed_fields, true)) {
                        echo 'unindex field ' . $filter['content']['field'];

                        return false;
                    }

                    //we can only do 'contains' on junction_aa
                    if ($filter['op'] == 'contains' && $filter['content']['field'] != $airr_names['junction_aa']) {
                        echo 'bad contains ' . $filter['content']['field'];

                        return false;
                    }

                    //'in' only works on repertoire_id
                    if ($filter['op'] == 'in' && $filter['content']['field'] != $airr_names['ir_project_sample_id']) {
                        echo 'bad in on ' . $filter['content']['field'];

                        return false;
                    }

                    //'=' works on any indexed field - BUT - we have to make sure query only uses one
                    //  indexed field and repertoir_id
                    if ($filter['op'] == '=') {
                        if ($has_indexed) {
                            echo 'Attempt to AND multiple fields ' . var_dump($filters);

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
            // shouldn't get here
            echo 'no return';
            die();

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}