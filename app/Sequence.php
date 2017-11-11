<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Sequence extends Model
{
    protected $collection = 'sequence_data_newnames';
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
    //'junction_aa' => 'string',
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
    'junction_length_aa' => 'int',
    'productive' => 'int',
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
            if ($filtername == 'junction_aa') {
                $query = $query->where($filtername, '=', $filtervalue);
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
        if (empty($f['show_unproductive'])) {
            $query = $query->where('functional', 'like', 'productive%');
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
            $sample_id_query = $sample_id_query->whereIn('_id', $filter['ir_project_sample_id_list']);
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
            $sample['sequences'] = $counts[$sample['_id']];
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

    public static function csv($params)
    {
        set_time_limit(300);
        ini_set('memory_limit', '1G');

        $filename = sys_get_temp_dir() . '/' . uniqid() . '-' . date('Y-m-d_G-i-s', time()) . '.csv';

        $file = fopen($filename, 'w');
        fclose($file);

        return $filename;
    }
}
