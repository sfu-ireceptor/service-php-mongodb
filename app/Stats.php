<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Stats extends Model
{
    protected $collection;

    public function __construct()
    {
        if (isset($_ENV['DB_STATS_COLLECTION'])) {
            $this->collection = $_ENV['DB_STATS_COLLECTION'];
        } else {
            $this->collection = 'stat';
        }
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public static function rearrangementCountRequest($params)
    {
        // given an array of repertoires, find the count of rearrangements for each
        //  -as an optimization, iReceptor stores rearrangement counts in repertoire collection
        //   for each set of rearrangement files, identified by the field mapped by
        //   service_name field ir_project_sample_id (default is repertoire_id but could be others)
        //  -for each repertoire id given, we can find all the repertoire entires and extract counts.
        //   This is faster than doing a search on stats

        $repertoire_service_to_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $repertoire_db_types = FileMapping::createMappingArray('ir_repository', 'ir_repository_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Repertoire', 'IR_Repertoire']]);

        $sample_id_list = [];
        $sample_id_list['total'] = 0;
        $sample_id_list['counts'] = [];
        // if 'repertoires' is set, loop through it and create an array of repertoire ids to search
        if (isset($params['repertoires'])) {
            if (! is_array($params['repertoires'])) {
                return 'error';
            }

            $repertoire_id_list = [];

            foreach ($params['repertoires'] as $repertoire_object) {
                //each repertoire object has repertoire_id, optional sample_processing_id and
                //  optional data_processing id, that we can use to find the repertoire in repertoire collection
                if (isset($repertoire_object['repertoire_id']) && ! is_null($repertoire_object['repertoire_id'])) {
                    $repertoire_id = AirrUtils::typeConvertHelperRaw($repertoire_object['repertoire_id'], $repertoire_db_types[$repertoire_service_to_db_mapping['ir_project_sample_id']]);
                    $repertoire_query[$repertoire_service_to_db_mapping['ir_project_sample_id']] = $repertoire_id;
                } else {
                    //repertoire id is required, fail if it's not provided
                    return 'error';
                }
                if (isset($repertoire_object['sample_processing_id'])) {
                    //we have sample_processing id, so return object should have it
                    $return_sample_processing = true;
                    if (! is_null($repertoire_object['sample_processing_id']) && $repertoire_object['sample_processing_id'] != 'all') {
                        $sample_processing_id = AirrUtils::typeConvertHelperRaw($repertoire_object['sample_processing_id'], $repertoire_db_types[$repertoire_service_to_db_mapping['sample_processing_id']]);
                        $repertoire_query[$repertoire_service_to_db_mapping['sample_processing_id']] = $sample_processing_id;
                    }
                }
                if (isset($repertoire_object['data_processing_id'])) {
                    //we have data_processing id, so return object should have it
                    $return_data_processing = true;
                    if (! is_null($repertoire_object['data_processing_id']) && $repertoire_object['data_processing_id'] != 'all') {
                        $data_processing_id = AirrUtils::typeConvertHelperRaw($repertoire_object['data_processing_id'], $repertoire_db_types[$repertoire_service_to_db_mapping['data_processing_id']]);
                        $repertoire_query[$repertoire_service_to_db_mapping['data_processing_id']] = $data_processing_id;
                    }
                }
                $repertoire_result = AirrRepertoire::findRepertoire($repertoire_query);
                foreach ($repertoire_result as $result) {
                    $sample_id_list['total'] += $result[$repertoire_service_to_db_mapping['ir_sequence_count']];
                    $return_object = [];
                    $return_object['repertoire_id'] = $result [$repertoire_service_to_db_mapping['ir_project_sample_id']];
                    if (isset($return_sample_processing)) {
                        $return_object['sample_processing_id'] = $result [$repertoire_service_to_db_mapping['sample_processing_id']];
                    }
                    if (isset($return_data_processing)) {
                        $return_object['data_processing_id'] = $result [$repertoire_service_to_db_mapping['data_processing_id']];
                    }
                    $return_object['count'] = $result[$repertoire_service_to_db_mapping['ir_sequence_count']];
                    $sample_id_list['counts'][] = $return_object;
                }
            }
        }

        return $sample_id_list;
    }

    public static function rearrangementCountResponse($count_list)
    {
        return json_encode($count_list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function statsRequest($params, $entry_point)
    {
        // given an array of repertoires, find the count of junction lengts for each
        $repertoire_service_to_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $repertoire_db_types = FileMapping::createMappingArray('ir_repository', 'ir_repository_type', ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Repertoire', 'IR_Repertoire']]);

        $stats_api_outputs = FileMapping::createMappingArray('service_name', 'irplus_stats_api_response', ['ir_cass'=>['IRPlus_stats', 'repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $junction_length_fields =
        $count_type = 'count';
        $entry_point_fields = [];

        switch ($entry_point) {
            case 'junction_length':
                $entry_point_fields = FileMapping::createMappingArray('service_name', 'irplus_stats_api_query', ['ir_subclass'=>['IRPlus_stats_junction_length']]);
                break;
            case 'gene_usage':
                $entry_point_fields = FileMapping::createMappingArray('service_name', 'irplus_stats_api_query', ['ir_subclass'=>['IRPlus_stats_gene_usage']]);
                break;
            case 'rearrangement_count':
                $entry_point_fields = FileMapping::createMappingArray('service_name', 'irplus_stats_api_query', ['ir_subclass'=>['IRPlus_stats_rearrangement_count']]);
                break;
            default:
                return 'error';
                break;
        }

        //check if the 'fields' parameter is set ,and fields are appropriate to the entry point
        if (isset($params['fields'])) {
            if (! is_array($params['fields'])) {
                return 'error';
            }
            foreach ($params['fields'] as $field_parameter) {
                if (! in_array($field_parameter, $entry_point_fields)) {
                    return 'error';
                }
            }
            $entry_point_fields = $params['fields'];
        }

        $sample_id_list = [];
        // if 'repertoires' is set, loop through it and create an array of repertoire ids to search
        if (isset($params['repertoires'])) {
            if (! is_array($params['repertoires']) || sizeof($params['repertoires']) == 0) {
                return 'error';
            }

            $repertoire_id_list = [];
            foreach ($params['repertoires'] as $repertoire_object) {
                //each repertoire object has repertoire_id, optional sample_processing_id and
                //  optional data_processing id, that we can use to find the repertoire in repertoire collection
                if (isset($repertoire_object['repertoire_id']) && ! is_null($repertoire_object['repertoire_id'])) {
                    $repertoire_id = AirrUtils::typeConvertHelperRaw($repertoire_object['repertoire_id'], $repertoire_db_types[$repertoire_service_to_db_mapping['ir_project_sample_id']]);
                    $repertoire_query[$repertoire_service_to_db_mapping['ir_project_sample_id']] = $repertoire_id;
                } else {
                    //repertoire id is required, fail if it's not provided
                    return 'error';
                }
                if (isset($repertoire_object['sample_processing_id'])) {
                    //we have sample_processing id, so return object should have it
                    $return_sample_processing = true;
                    if (! is_null($repertoire_object['sample_processing_id']) && $repertoire_object['sample_processing_id'] != 'all') {
                        $sample_processing_id = AirrUtils::typeConvertHelperRaw($repertoire_object['sample_processing_id'], $repertoire_db_types[$repertoire_service_to_db_mapping['sample_processing_id']]);
                        $repertoire_query[$repertoire_service_to_db_mapping['sample_processing_id']] = $sample_processing_id;
                    }
                }
                if (isset($repertoire_object['data_processing_id'])) {
                    //we have data_processing id, so return object should have it
                    $return_data_processing = true;
                    if (! is_null($repertoire_object['data_processing_id']) && $repertoire_object['data_processing_id'] != 'all') {
                        $data_processing_id = AirrUtils::typeConvertHelperRaw($repertoire_object['data_processing_id'], $repertoire_db_types[$repertoire_service_to_db_mapping['data_processing_id']]);
                        $repertoire_query[$repertoire_service_to_db_mapping['data_processing_id']] = $data_processing_id;
                    }
                }
                $repertoire_result = AirrRepertoire::findRepertoire($repertoire_query);
                foreach ($repertoire_result as $repertoire) {
                    $response_object = [];
                    $repertoire_id = $repertoire['_id'];
                    $sample_processing_id = $repertoire['sample_processing_id'];
                    $data_processing_id = $repertoire['data_processing_id'];
                    $connector_id = $repertoire['_id'];

                    $response_object['repertoires']['repertoire_id'] = strval($repertoire_id);
                    $response_object['repertoires']['sample_processing_id'] = strval($sample_processing_id);
                    $response_object['repertoires']['data_processing_id'] = strval($data_processing_id);

                    foreach ($entry_point_fields as $current_field) {
                        $stats_object = [];
                        $stats_object['statistic_name'] = $current_field;
                        $stats_object['total'] = 0;
                        $stats_query = new self();

                        $stat_total = 0;
                        $stats_query = $stats_query->where('ir_project_sample_id', '=', $connector_id);
                        $stats_query = $stats_query->where('name', '=', $current_field);
                        $stats_results = $stats_query->get();
                        foreach ($stats_results as $stat) {
                            $value = $stat['value'];
                            $count = $stat['count'];
                            $stat_total += $count;

                            $stats_object['data'][] = ['key'=>$value, 'count'=>$count];
                        }
                        $stats_object['total'] = $stat_total;
                        $response_object['statistics'][] = $stats_object;
                    }
                    $sample_id_list[] = $response_object;
                }
            }
        }

        return $sample_id_list;
    }
}
