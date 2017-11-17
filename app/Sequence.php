<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Sequence extends Model
{
    protected $collection;

    public function __construct()
    {
        if (isset($_ENV['DB_SEQUENCES_COLLECTION'])) {
            $this->collection = $_ENV['DB_SEQUENCES_COLLECTION'];
        } else {
            $this->collection = 'sequences';
        }
    }

    public $timestamps = false;
    protected $max_results = 25;

    public static $coltype = [
    'v_call' => 'string',
    'd_call' => 'string',
    'j_call' => 'string',
    'vgene' => 'string',
    'seq_id' => 'int',
    'seq_name' => 'string',
    'ir_project_sample_id' => 'int',
    'id' => 'int',
    'sequence_id' => 'int',
    'vgene_string' => 'string',
    'vgene_family' => 'string',
    'vgene_gene' => 'string',
    'vgene_allele' => 'string',
    'jgene_string' => 'string',
    'jgene_family' => 'string',
    'jgene_gene' => 'string',
    'jgene_allele' => 'string',
    'dgene_string' => 'string',
    'dgene_family' => 'string',
    'dgene_gene' => 'string',
    'dgene_allele' => 'string',
    'functional' => 'string',
    'functionality_comment' => 'string',
    'orientation' => 'string',
    'vgene_score' => 'int',
    'vgene_probability' => 'int',
    'dregion_reading_frame' => 'string',
    'cdr1_length' => 'int',
    'cdr2_length' => 'int',
    'cdr3_length' => 'int',
    'vdjregion_sequence_nt' => 'string',
    'vjregion_sequence_nt' => 'string',
    'djregion_sequence_nt' => 'string',
    'vregion_sequence_nt' => 'string',
    'jregion_sequence_nt' => 'string',
    'dregion_sequence_nt' => 'string',
    'fr1region_sequence_nt' => 'string',
    'fr2region_sequence_nt' => 'string',
    'fr3region_sequence_nt' => 'string',
    'fr4region_sequence_nt' => 'string',
    'cdr1region_sequence_nt' => 'string',
    'cdr2region_sequence_nt' => 'string',
    'cdr3region_sequence_nt' => 'string',
    'junction_nt' => 'string',
    'vdjregion_sequence_nt_gapped' => 'string',
    'vjregion_sequence_nt_gapped' => 'string',
    'vregion_sequence_nt_gapped' => 'string',
    'jregion_sequence_nt_gapped' => 'string',
    'dregion_sequence_nt_gapped' => 'string',
    'fr1region_sequence_nt_gapped' => 'string',
    'fr2region_sequence_nt_gapped' => 'string',
    'fr3region_sequence_nt_gapped' => 'string',
    'fr4region_sequence_nt_gapped' => 'string',
    'cdr1region_sequence_nt_gapped' => 'string',
    'cdr2region_sequence_nt_gapped' => 'string',
    'cdr3region_sequence_nt_gapped' => 'string',
    'junction_sequence_nt_gapped' => 'string',
    'vdjregion_sequence_aa' => 'string',
    'vjregion_sequence_aa' => 'string',
    'vregion_sequence_aa' => 'string',
    'jregion_sequence_aa' => 'string',
    'fr1region_sequence_aa' => 'string',
    'fr2region_sequence_aa' => 'string',
    'fr3region_sequence_aa' => 'string',
    'fr4region_sequence_aa' => 'string',
    'cdr1region_sequence_aa' => 'string',
    'cdr2region_sequence_aa' => 'string',
    'cdr3region_sequence_aa' => 'string',
    'junction_aa' => 'string',
    'vdjregion_sequence_aa_gapped' => 'string',
    'vjregion_sequence_aa_gapped' => 'string',
    'vregion_sequence_aa_gapped' => 'string',
    'jregion_sequence_aa_gapped' => 'string',
    'fr1region_sequence_aa_gapped' => 'string',
    'fr2region_sequence_aa_gapped' => 'string',
    'fr3region_sequence_aa_gapped' => 'string',
    'fr4region_sequence_aa_gapped' => 'string',
    'cdr1region_sequence_aa_gapped' => 'string',
    'cdr2region_sequence_aa_gapped' => 'string',
    'cdr3region_sequence_aa_gapped' => 'string',
    'junction_sequence_aa_gapped' => 'string',
    'vdjregion_start' => 'int',
    'vdjregion_end' => 'int',
    'vjregion_start' => 'int',
    'vjregion_end' => 'int',
    'djregion_start' => 'int',
    'djregion_end' => 'int',
    'vregion_start' => 'int',
    'vregion_end' => 'int',
    'jregion_start' => 'int',
    'jregion_end' => 'int',
    'dregion_start' => 'int',
    'dregion_end' => 'int',
    'fr1region_start' => 'int',
    'fr1region_end' => 'int',
    'fr2region_start' => 'int',
    'fr2region_end' => 'int',
    'fr3region_start' => 'int',
    'fr3region_end' => 'int',
    'fr4region_start' => 'int',
    'fr4region_end' => 'int',
    'cdr1region_start' => 'int',
    'cdr1region_end' => 'int',
    'cdr2region_start' => 'int',
    'cdr2region_end' => 'int',
    'cdr3region_start' => 'int',
    'cdr3region_end' => 'int',
    'junction_start' => 'int',
    'junction_end' => 'int',
    'vregion_mutation_string' => 'string',
    'fr1region_mutation_string' => 'string',
    'fr2region_mutation_string' => 'string',
    'fr3region_mutation_string' => 'string',
    'cdr1region_mutation_string' => 'string',
    'cdr2region_mutation_string' => 'string',
    'cdr3region_mutation_string' => 'string',
    'junction_length' => 'int',
    'junction_aa_length' => 'int',
    'productive' => 'int',
    ];

    public static $header_fields = [
        'seq_id',
        'sequence_nt',
        'seq_name',
        'id',
        'sequence_id',
        'vgene_string',
        'vgene_family',
        'vgene_gene',
        'v_call',
        'jgene_string',
        'jgene_family',
        'jgene_gene',
        'j_call',
        'dgene_string',
        'dgene_family',
        'dgene_gene',
        'd_call',
        'functional',
        'v_score',
        'vgene_probablity',
        'dregion_reading_frame',
        'cdr1_length',
        'cdr2_length',
        'cdr3_length',
        'vdjregion_sequence_nt',
        'vjregion_sequence_nt',
        'djregion_sequence_nt',
        'vregion_sequence_nt',
        'jregion_sequence_nt',
        'dregion_sequence_nt',
        'fr1region_sequence_nt',
        'fr2region_sequence_nt',
        'fr3region_sequence_nt',
        'fr4region_sequence_nt',
        'cdr1region_sequence_nt',
        'cdr2region_sequence_nt',
        'cdr3region_sequence_nt',
        'junction_nt',
        'vdjregion_sequence_nt_gapped',
        'vjregion_sequence_nt_gapped',
        'djregion_sequence_nt_gapped',
        'vregion_sequence_nt_gapped',
        'jregion_sequence_nt_gapped',
        'dregion_sequence_nt_gapped',
        'fr1region_sequence_nt_gapped',
        'fr2region_sequence_nt_gapped',
        'fr3region_sequence_nt_gapped',
        'fr4region_sequence_nt_gapped',
        'cdr1region_sequence_nt_gapped',
        'cdr2region_sequence_nt_gapped',
        'cdr3region_sequence_nt_gapped',
        'junction_sequence_nt_gapped',
        'vdjregion_sequence_aa',
        'vjregion_sequence_aa',
        'djregion_sequence_aa',
        'vregion_sequence_aa',
        'jregion_sequence_aa',
        'dregion_sequence_aa',
        'fr1region_sequence_aa',
        'fr2region_sequence_aa',
        'fr3region_sequence_aa',
        'fr4region_sequence_aa',
        'cdr1region_sequence_aa',
        'cdr2region_sequence_aa',
        'cdr3region_sequence_aa',
        'junction_aa',
        'vdjregion_sequence_aa_gapped',
        'vjregion_sequence_aa_gapped',
        'djregion_sequence_aa_gapped',
        'vregion_sequence_aa_gapped',
        'jregion_sequence_aa_gapped',
        'dregion_sequence_aa_gapped',
        'fr1region_sequence_aa_gapped',
        'fr2region_sequence_aa_gapped',
        'fr3region_sequence_aa_gapped',
        'fr4region_sequence_aa_gapped',
        'cdr1region_sequence_aa_gapped',
        'cdr2region_sequence_aa_gapped',
        'cdr3region_sequence_aa_gapped',
        'junction_sequence_aa_gapped',
        'vdjregion_start',
        'vdjregion_end',
        'vjregion_start',
        'vjregion_end',
        'v_start',
        'v_end',
        'j_start',
        'j_end',
        'd_start',
        'd_end',
        'fwr1_start',
        'fwr1_end',
        'fwr2_start',
        'fwr2_end',
        'fwr3_start',
        'fwr3_end',
        'fwr4_start',
        'fwr4_end',
        'cdr1_start',
        'cdr1_end',
        'cdr2_start',
        'cdr2_end',
        'cdr3_start',
        'cdr3_end',
        'junction_start',
        'junction_end',
        'vregion_mutation_string',
        'fr1region_mutation_string',
        'fr2region_mutation_string',
        'fr3region_mutation_string',
        'cdr1region_mutation_string',
        'cdr2region_mutation_string',
        'cdr3region_mutation_string',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'functionality_comment',
        'rev_comp',
        'vgene_probability',
        'djregion_start',
        'djregion_end',
        'annotation_tool',
        'annotation_date',
        'tool_version',
        'reference_version',
        'species',
        'receptor_type',
        'reference_directory_set',
        'search_insert_delete',
        'no_nucleotide_to_add',
        'no_nucleotide_to_exclude',
        'ir_project_sample_id',
        'junction_aa_length',
        'junction_nt_length',
        'productive',
        'subject_id',
        'ir_subject_id',
        'sex',
        'organism',
        'ethnicity',
        'ir_project_id',
        'study_title',
        'ir_project_parent_id',
        'study_id',
        'study_description',
        'ir_lab_id',
        'lab_name',
        'ir_disease_state_id',
        'disease_state_sample',
        'ir_case_control_id',
        'study_group_description',
        'ir_sequence_count',
        'ir_project_sample_note',
        'ir_sra_run_id',
        'sample_id',
        'ir_subject_age',
        'ir_sample_subject_id',
        'ir_dna_id',
        'template_class',
        'ir_sample_source_id',
        'tissue',
        'ir_lab_cell_subset_name',
        'cell_subset',
        'sequencing_platform',
        'cell_phenotype',
        'db_name',
    ];

    public static function parseFilter(&$query, $f)
    {
        if (isset($f['ir_project_sample_id_list'])) {
            $int_ids = [];

            $query = $query->whereIn('ir_project_sample_id', array_map('intval', $f['ir_project_sample_id_list']));
        }
        foreach ($f as $filtername => $filtervalue) {
            if ($filtername == 'ir_project_sample_id_list') {
                continue;
            }
            if ($filtername == 'functional') {
                $query = $query->whereIn('productive', 'is', 'true');
                contunie;
            }
            if ($filtername == 'junction_aa') {
                $query = $query->where($filtername, 'like', "%$filtervalue%");
                continue;
            }
            if (empty(self::$coltype[$filtername]) || $filtervalue == '') {
                continue;
            }
            if (self::$coltype[$filtername] == 'string') {
                $query = $query->where($filtername, 'like', '%' . $filtervalue . '%');
                if ($filtername == 'junction_aa') {
                    die();
                }
            }
            if (self::$coltype[$filtername] == 'int') {
                $query = $query->where($filtername, '=', (int) $filtervalue);
            }
        }
        if (empty($f['functional'])) {
            $query = $query->where('productive', 'is', 'true');
        }
    }

    public static function aggregate($filter)
    {
        $query = new self();
        $psa_list = [];
        $counts = [];
        //self::parseFilter($query, $filter);
        //$result = $query->groupBy('project_sample_id')->get();
        $sample_id_query = new Sample();
        if (isset($filter['ir_project_sample_id_list'])) {
            $sample_id_query = $sample_id_query->whereIn('_id', array_map('intval', $filter['ir_project_sample_id_list']));
        }
        $result = $sample_id_query->get();
        foreach ($result as $psa) {
            $count_query = new self();
            self::parseFilter($count_query, $filter);
            $count_query = $count_query->where('ir_project_sample_id', '=', $psa['_id']);
            $total = $count_query->count();
            if ($total > 0) {
                $psa_list[] = $psa['_id'];
                $counts[$psa['_id']] = $total;
            }
        }
        $sample_query = new Sample();
        $sample_rows = $sample_query->whereIn('_id', $psa_list)->get();
        $sample_metadata = [];
        foreach ($sample_rows as $sample) {
            $sample['ir_filtered_sequence_count'] = $counts[$sample['_id']];
            $sample_metadata[] = $sample;
        }

        return $sample_metadata;
    }

    public static function list($f)
    {
        $query = new self();

        $num_results = 25;
        $start_at = 0;

        self::parseFilter($query, $f);

        if (! empty($f['page_number']) && ($f['page_number'] > 0)) {
            $start_at = $f['page_number'] - 1;
        }
        if (! empty($f['num_results']) && ($f['num_results'] > 0)) {
            $num_results = $f['num_results'];
        }

        $result = $query->skip($start_at * $num_results)->take($num_results)->get();
        foreach ($result as $row) {
            if (is_array($row['v_call'])) {
                $row['v_call'] = implode(', or ', $row['v_call']);
            }
            if (is_array($row['j_call'])) {
                $row['j_call'] = implode(', or ', $row['j_call']);
            }
            if (is_array($row['d_call'])) {
                $row['d_call'] = implode(', or ', $row['d_call']);
            }
        }

        return $result;
    }

    public static function count($f)
    {
        $query = new self();

        self::parseFilter($query, $f);

        return $query->count();
    }

    public static function data($params)
    {
        set_time_limit(300);
        ini_set('memory_limit', '1G');

        $filename = sys_get_temp_dir() . '/' . uniqid() . '-' . date('Y-m-d_G-i-s', time()) . '.csv';

        $file = fopen($filename, 'w');

        $query = new self();
        $psa_list = [];
        $sample_id_query = new Sample();
        if (isset($params['ir_project_sample_id_list'])) {
            $sample_id_query = $sample_id_query->whereIn('_id', array_map('intval', $params['ir_project_sample_id_list']));
        }
        $result = $sample_id_query->get();
        foreach ($result as $psa) {
            $psa_list[$psa['_id']] = $psa;
        }

        fputcsv($file, self::$header_fields, ',');

        $query = new self();
        self::parseFilter($query, $params);
        $result = $query->get();

        foreach ($result as $row) {
            $sequence_list = $row->toArray();
            $results_array = [];
            $sample_array = $psa_list[$sequence_list['ir_project_sample_id']];
            $results_array = array_merge($sequence_list, $sample_array->toArray());

            $new_line = [];
            foreach (self::$header_fields as $current_header) {
                if (isset($results_array[$current_header])) {
                    if (is_array($results_array[$current_header])) {
                        $new_line[$current_header] = implode($results_array[$current_header], ', or');
                    } else {
                        $new_line[$current_header] = $results_array[$current_header];
                    }
                } else {
                    $new_line[$current_header] = '';
                }
            }
            fputcsv($file, $new_line, ',');
        }

        fclose($file);

        return $filename;
    }
}
