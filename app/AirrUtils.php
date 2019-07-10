<?php

//class that contains various utility functions for AIRR API 
namespace App;

use Illuminate\Database\Eloquent\Model;

class AirrUtils extends Model
{

    public static function processAirrFilter($f, $service_to_airr_array, $airr_types_array)
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
}
?>