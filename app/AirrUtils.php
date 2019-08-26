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
                        if ($content['field'] == 'repertoire_id')
                        {
                            $value = json_encode(array_map('intval', $content['value']));
                        }
                        else
                        {

                            $value = json_encode($content['value']);
                        }
                    } else {
                        if ($content['field'] == 'repertoire_id')
                        {
                            $value = '"' . (int)$content['value'] . '"';
                        }
                        else
                        {
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
                    return '{"' . $field . '":{"$le":' . $value . '}}';
                } else {
                    return;
                }
            case '>=':
                if ($field != '' && $value != '') {
                    return '{"' . $field . '":{"$ge":' . $value . '}}';
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
}
