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

    public static function statsRequest($params, $entry_point)
    {
        // given an array of repertoires, find the count of junction lengts for each
        $repertoire_service_to_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository',
            ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
        $repertoire_db_types = FileMapping::createMappingArray('ir_repository', 'ir_repository_type',
            ['ir_class'=>['rearrangement', 'ir_rearrangement', 'Repertoire', 'IR_Repertoire']]);
        $stats_api_outputs = FileMapping::createMappingArray('irplus_stats_api_query', 'irplus_stats_api_response',
            ['ir_class'=>['IRPlus_stats']]);
        $stats_api_input_to_db_mapping = FileMapping::createMappingArray('irplus_stats_api_query', 'ir_repository',
            ['ir_class'=>['IRPlus_stats']]);
        $service_to_api_input_mapping = FileMapping::createMappingArray('service_name', 'irplus_stats_api_query',
             ['ir_class'=>['IRPlus_stats']]);
        $service_to_api_output_mapping = FileMapping::createMappingArray('service_name', 'irplus_stats_api_response',
            ['ir_class'=>['IRPlus_stats']]);
        $service_to_stats_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository',
            ['ir_class'=>['IRPlus_stats']]);
        $service_to_repertoire_db_mapping = FileMapping::createMappingArray('service_name', 'ir_repository', ['ir_class'=>['repertoire', 'ir_repertoire', 'Repertoire', 'IR_Repertoire']]);
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

        //check if the 'statistics' parameter is set ,and statistics are appropriate to the entry point
        if (isset($params[$service_to_api_input_mapping['statistics']])) {
            if (! is_array($params[$service_to_api_input_mapping['statistics']])) {
                return 'error';
            }
            foreach ($params[$service_to_api_input_mapping['statistics']] as $field_parameter) {
                if (! in_array($field_parameter, $entry_point_fields)) {
                    return 'error';
                }
            }
            $entry_point_fields = $params[$service_to_api_input_mapping['statistics']];
        }

        $sample_id_list = [];
        // non-count entry points require 'repertoires' parameter
        if (! isset($params[$service_to_api_input_mapping['repertoires']])) {
            if ($entry_point != 'rearrangement_count') {
                return 'error';
            } else {
                $all_repertoires = AirrRepertoire::findRepertoire([]);
                foreach ($all_repertoires as $repertoire) {
                    //only return repertoires that have stats
                    if ($repertoire[$service_to_repertoire_db_mapping['ir_sequence_count']] > 0) {
                        $repertoire_id = $repertoire[$service_to_repertoire_db_mapping['ir_project_sample_id']];
                        $sample_processing_id = $repertoire[$service_to_repertoire_db_mapping['sample_processing_id']];
                        $data_processing_id = $repertoire[$service_to_repertoire_db_mapping['data_processing_id']];
                        $repertoire_all_object = ['repertoire_id' => $repertoire_id,
                            'sample_processing_id' => $repertoire[$service_to_repertoire_db_mapping['sample_processing_id']],
                            'data_processing_id' => $repertoire[$service_to_repertoire_db_mapping['data_processing_id']], ];
                        $params[$service_to_api_input_mapping['repertoires']][]['repertoire'] = $repertoire_all_object;
                    }
                }
                unset($all_repertoires);
            }
        }

        // if 'repertoires' is set, loop through it and create an array of repertoire ids to search
        //  otherwise, loop through entire repertoires collection
        if (isset($params[$service_to_api_input_mapping['repertoires']])) {
            if (! is_array($params[$service_to_api_input_mapping['repertoires']]) || sizeof($params[$service_to_api_input_mapping['repertoires']]) == 0) {
                return 'error';
            }

            foreach ($params[$service_to_api_input_mapping['repertoires']] as $repertoire_element) {
                //each repertoire object has repertoire_id, optional sample_processing_id and
                //  optional data_processing id, that we can use to find the repertoire in repertoire collection
                if (isset($repertoire_element[$service_to_api_input_mapping['repertoire']])) {
                    $repertoire_object = $repertoire_element[$service_to_api_input_mapping['repertoire']];
                } else {
                    return 'error';
                }
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
                    $repertoire_id = $repertoire[$service_to_repertoire_db_mapping['ir_project_sample_id']];
                    $sample_processing_id = $repertoire[$service_to_repertoire_db_mapping['sample_processing_id']];
                    $data_processing_id = $repertoire[$service_to_repertoire_db_mapping['data_processing_id']];
                    $connector_id = $repertoire[$service_to_repertoire_db_mapping['ir_annotation_set_metadata_id']];

                    $response_object['repertoires']['repertoire_id'] = strval($repertoire_id);
                    $response_object['repertoires']['sample_processing_id'] = strval($sample_processing_id);
                    $response_object['repertoires']['data_processing_id'] = strval($data_processing_id);

                    foreach ($entry_point_fields as $current_field) {
                        $stats_object = [];
                        $stats_object[$service_to_api_output_mapping['statistic_name']] = $stats_api_outputs[$current_field];
                        $stats_object[$service_to_api_output_mapping['total']] = 0;
                        $stats_object[$service_to_api_output_mapping['data']] = [];
                        $stats_query = new self();

                        $stat_total = 0;
                        $stats_query = $stats_query->where($service_to_stats_db_mapping['ir_project_sample_id'], '=', $connector_id);
                        $stats_query = $stats_query->where($service_to_stats_db_mapping['statistic_name'], '=', $stats_api_input_to_db_mapping[$current_field]);
                        $stats_results = $stats_query->get();
                        foreach ($stats_results as $stat) {
                            $value = strval($stat['value']);
                            $count = intval($stat['count']);
                            $stat_total += $count;

                            $stats_object[$service_to_api_output_mapping['data']][] = [$service_to_api_output_mapping['key']=>$value,
                                $service_to_api_output_mapping['value']=>$count, ];
                        }
                        $stats_object[$service_to_api_output_mapping['total']] = $stat_total;
                        $response_object[$service_to_api_output_mapping['statistics']][] = $stats_object;
                    }
                    $sample_id_list[] = $response_object;
                }
            }
        }

        return $sample_id_list;
    }
}
