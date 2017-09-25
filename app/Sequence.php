<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Sequence extends Model
{
    protected $collection = 'sequences';
    public $timestamps = false;
    protected $max_results = 25;

    public static $coltype = [
    'seq_id' => 'int',
    'seq_name' => 'string',
    'project_sample_id' => 'int',
    'id' => 'int',
    'sequence_id' => 'int',
    'vgene_string' => 'string',
    'vgene_family' => 'int',
    'vgene_gene' => 'int',
    'vgene_allele' => 'string',
    'jgene_string' => 'string',
    'jgene_family' => 'string',
    'jgene_gene' => 'string',
    'jgene_allele' => 'string',
    'dgene_string' => 'string',
    'dgene_family' => 'string',
    'dgene_gene' => 'string',
    'dgene_allele' => 'string',
    'functionality' => 'string',
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
    'junction_sequence_nt' => 'string',
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
    'junction_sequence_aa' => 'string',
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
    ];

    public static function parseFilter(&$query, $f)
    {
        if (isset($f['project_sample_id_list'])) {
            $query = $query->whereIn('project_sample_id', $f['project_sample_id_list']);
        }
        foreach ($f as $filtername => $filtervalue) {
            if (empty(self::$coltype[$filtername]) || $filtervalue == '') {
                continue;
            }
            if ($filtername == 'project_sample_id_list') {
                continue;
            }
            if (self::$coltype[$filtername] == 'string') {
                $query = $query->where($filtername, 'like', '%' . $filtervalue . '%');
            }
            if (self::$coltype[$filtername] == 'int') {
                $query = $query->where($filtername, '=', $filtervalue);
            }
        }
        if (empty($f['show_unproductive'])) {
            $query = $query->where('functionality', 'like', 'productive%');
        }
    }

    public static function aggregate($filter)
    {
        $query = new self();
        $psa_list = [];
        $counts = [];
        self::parseFilter($query, $filter);
        /*$result = $query::raw()->aggregate(array(

                    array('$group' =>  array('_id' =>  '$project_sample_id', 'count'=> array('$sum' =>1 )))

                         ));        */

        //var_dump($result);
        $result = $query->groupBy('project_sample_id')->get();

        foreach ($result as $psa) {
            //var_dump($psa);
            $psa_list[] = $psa['project_sample_id'];
            $counts[$psa['project_sample_id']] = $psa['total'];
        }
        $sample_query = new Sample();
        $sample_rows = $sample_query->whereIn('project_sample_id', $psa_list)->get();
        $sample_metadata = [];
        foreach ($sample_rows as $sample) {
            $sample['sequences'] = $counts[$sample['project_sample_id']];
            $sample_metadata[$sample['project_sample_id']] = $sample;
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

        return $query->skip($start_at * $num_results)->take($num_results)->get();
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
