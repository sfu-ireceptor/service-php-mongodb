<?php

namespace App;

use Log;
use Illuminate\Support\Facades\DB;
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

    public function getTempFolder()
    {
        return $this->temp_files;
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
    'annotation_tool' => 'string',
    'junction_length' => 'int',
    'junction_aa_length' => 'int',
    'functional' => 'int',
    'ir_annotation_tool' => 'string',
    'sequence'=>'string',
    ];

    public static $header_fields = [
        'seq_id',
        'sequence',
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
        'functionality',
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
        'junction_length',
        'functional',
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

    public static $airr_headers = [
       'sequence'=>'sequence',
       'sequence_id'=>'seq_name',
       'rearrangement_id'=>'NULL',
       'rev_comp'=>'rev_comp',
       'sequence_alignment'=>'NULL',
       'germline_alignment'=>'NULL',
       'v_call'=>'v_call',
       'j_call'=>'j_call',
       'd_call'=>'d_call',
       'c_call'=>'NULL',
       'v_score'=>'v_score',
       'd_score'=>'NULL',
       'j_score'=>'NULL',
       'c_score'=>'NULL',
       'junction'=>'junction',
       'junction_length'=>'junction_length',
       'v_cigar'=>'NULL',
       'j_cigar'=>'NULL',
       'd_cigar'=>'NULL',
       'c_cigar'=>'NULL',
       'cdr1_aa'=>'cdr1region_sequence_aa',
       'cdr2_aa'=>'cdr2region_sequence_aa',
       'cdr3_aa'=>'cdr3region_sequence_aa',
       'junction_aa'=>'junction_aa',
       'junction_aa_length'=>'junction_aa_length',
       'productive'=>'functional',
       'functional'=>'functional',
       'subject_id'=>'subject_id',
       'sex'=>'sex',
       'organism'=>'organism',
       'ethnicity'=>'ethnicity',
       'study_title'=>'study_title',
       'study_id'=>'study_id',
       'study_description'=>'study_description',
       'lab_name'=>'lab_name',
       'disease_state_sample'=>'disease_state_sample',
       'study_group_description'=>'study_group_description',
       'sample_id'=>'sample_id',
       'template_class'=>'template_class',
       'tissue'=>'tissue',
       'cell_subset'=>'cell_subset',
       'sequencing_platform'=>'sequencing_platform',
       'cell_phenotype'=>'cell_phenotype',
    ];

    public static function parseFilter(&$query, $f)
    {
        foreach ($f as $filtername => $filtervalue) {
            if ($filtername == 'ir_project_sample_id_list') {
                continue;
            }
            if ($filtername == 'functional') {
                if (lc($filtervalue) == 'true') {
                    $query = $query->where($filtername, '=', 1);
                } elseif (lc($filtervalue) == 'false') {
                    $query = $query->where($filtername, '=', 0);
                } else {
                    $query = $query->where($filtername, '=', (int) $filtervalue);
                }
                continue;
            }
            if ($filtername == 'junction_aa') {
                $query = $query->where('substring', '=', $filtervalue);
                continue;
            }
            if (in_array($filtername, ['v_call', 'j_call', 'd_call'])) {
                //$query = $query->where($filtername, 'like', $filtervalue . '%');
                $filtervalue = trim($filtervalue);

                $query = $query->where($filtername, '>=', $filtervalue);
                $filtervalue_right = ord(substr($filtervalue, -1, 1));
                $filtervalue_right++;

                $filtervalue_upper = substr_replace($filtervalue, chr($filtervalue_right), -1);
                $query = $query->where($filtername, '<', $filtervalue_upper);

                continue;
            }
            if (empty(self::$coltype[$filtername]) || $filtervalue == '') {
                continue;
            }
            if ($filtername == 'ir_annotation_tool') {
                $query = $query->where('annotation_tool', '=', $filtervalue);
            }
            if (self::$coltype[$filtername] == 'string') {
                $query = $query->where($filtername, 'like', '%' . $filtervalue . '%');
            }
            if (self::$coltype[$filtername] == 'int') {
                $query = $query->where($filtername, '=', (int) $filtervalue);
            }
        }
        if (! isset($f['functional'])) {
            //$query = $query->where('functional', '=', 1);
        }
    }

    public static function SequenceMatch($id, $f)
    {
        $return_match = [];

        $return_match['ir_project_sample_id'] = (int) $id;
        foreach ($f as $filtername => $filtervalue) {
            if ($filtername == 'ir_project_sample_id_list') {
                continue;
            }
            if ($filtername == 'functional') {
                $filtervalue = trim($filtervalue);
                if ($filtervalue == 'true') {
                    $return_match['functional'] = 1;
                } elseif ($filtervalue == 'false') {
                    $return_match['functional'] = 0;
                } else {
                    $return_match['functional'] = (int) $filtervalue;
                }

                continue;
            }

            if ($filtername == 'junction_aa') {
                $filtervalue = trim($filtervalue);

                $return_match['substring'] = $filtervalue;
                continue;
            }
            if ($filtername == 'annotation_tool') {
                $return_match['ir_annotation_tool'] = $filtervalue;
                continue;
            }
            if ($filtername == 'ir_annotation_tool') {
                $return_match['ir_annotation_tool'] = $filtervalue;
                continue;
            }
            if (empty(self::$coltype[$filtername]) || $filtervalue == '') {
                continue;
            }
            if (in_array($filtername, ['v_call', 'j_call', 'd_call'])) {
                //$filtervalue = preg_quote($filtervalue);
                $filtervalue = trim($filtervalue);
                preg_match('/(.)_/', $filtername, $gene_prefix);
                $gene_to_filter = $gene_prefix[1];
                if (preg_match("/\*/", $filtervalue)) {
                    $gene_to_filter = $gene_to_filter . '_call';
                } elseif (preg_match("/\-/", $filtervalue)) {
                    $gene_to_filter = $gene_to_filter . 'gene_gene';
                } else {
                    $gene_to_filter = $gene_to_filter . 'gene_family';
                }
                $return_match[$gene_to_filter] = $filtervalue;
                continue;
                //$return_match[$filtername]['$regex'] = '^' . $filtervalue . '.*';
                //$return_match[$filtername]['$options'] = 'i';
                //$filtervalue = preg_replace("/\*/", '\\*', $filtervalue);
                //$return_match[$filtername]['$regex'] = '^' . $filtervalue;
                /*$filtervalue_right = ord(substr($filtervalue, -1, 1));
                $filtervalue_right++;


                $filtervalue_upper = substr_replace($filtervalue, chr($filtervalue_right), -1);

                $return_match[$filtername]['$gte'] = $filtervalue;
                $return_match[$filtername]['$lt'] = $filtervalue_upper;*/
                continue;
            }

            if (self::$coltype[$filtername] == 'string') {
                $filtervalue = trim($filtervalue);

                $filtervalue = preg_quote($filtervalue);
                $return_match[$filtername]['$regex'] = '.*' . $filtervalue . '.*';
                $return_match[$filtername]['$options'] = 'i';
                continue;
            }
            if (self::$coltype[$filtername] == 'int') {
                $return_match[$filtername] = (int) $filtervalue;
                continue;
            }
        }
        if (! isset($f['functional'])) {
            //$return_match['functional'] = 1;
        }

        return $return_match;
    }

    public static function aggregate($filter)
    {
        $query = new self();
        $psa_list = [];
        $counts = [];
        $start_request = microtime(true);
        $sample_metadata = [];
        $match = [];
        $sample_id_query = new Sample();
        if (isset($filter['ir_project_sample_id_list'])) {
            $sample_id_query = $sample_id_query->whereIn('_id', array_map('intval', $filter['ir_project_sample_id_list']));
        }

        // quick check to see if we have a filter that's not ir_project_sample_id_list
        //   if we don't, we can just use pre-computed sequence counts
        $has_filter = false;

        foreach ($filter as $filtername=>$filtervalue) {
            if (array_key_exists($filtername, self::$coltype)) {
                $has_filter = true;
            }
        }
        $count_timeout = $query->getCountTimeout();
        $sample_id_query = $sample_id_query->where('ir_sequence_count', '>', 0);
        $result = $sample_id_query->get();
        foreach ($result as $psa) {
            //DB::enableQueryLog();
            $total = $psa['ir_sequence_count'];
            if ($has_filter) {
                $sequence_match = self::SequenceMatch($psa['_id'], $filter);
                $query_params = [];

                $query_params['maxTimeMS'] = $count_timeout;

                $start = microtime(true);
                try {
                    $total = DB::collection($query->getCollection())->raw()->count($sequence_match, $query_params);
                } catch (\Exception $e) {
                    return -1;
                }
                $time = microtime(true) - $start;
                $logid = $psa['_id'];
                if (isset($sequence_match['substring'])) {
                    Log::error("For sample id $logid time was $time count was $total and junction was " . $sequence_match['substring']);
                } else {
                    Log::error("For sample id $logid time was $time count was $total ");
                }
            }

            //dd(DB::getQueryLog());
            if ($total > 0) {
                $psa['ir_filtered_sequence_count'] = $total;
                $psa_list[] = $psa;
            }
            $total_time = (microtime(true) - $start_request) * 1000;
            if ($total_time > $count_timeout && $count_timeout > 0) {
                Log::error("$total_time exceeded $count_timeout");

                return -1;
            }
        }

        return $psa_list;
    }

    public static function list($f, $sample_list)
    {
        $query = new self();

        $num_results = 25;
        $start_at = 0;
        $current_results = 0;
        $result = [];
        $return_array = [];
        foreach ($sample_list as $sample) {
            $needed_results = $num_results - $current_results;
            if ($needed_results < 1) {
                break;
            }
            $sequence_match = self::SequenceMatch($sample['_id'], $f);
            $result = DB::collection($query->getCollection())->raw()->find($sequence_match, ['limit'=>$needed_results]);
            foreach ($result as $sequence) {
                $return_array[] = $sequence;
                $current_results++;
            }
        }
        foreach ($return_array as $row) {
            $row['_id'] = (string) $row['_id'];
            if (! is_string($row['v_call']) && ! is_null($row['v_call'])) {
                $row['v_call'] = $row['v_call']->jsonSerialize();
            }
            if (is_array($row['v_call'])) {
                $row['v_call'] = implode(', or ', $row['v_call']);
            }
            if (! is_string($row['j_call']) && ! is_null($row['j_call'])) {
                $row['j_call'] = $row['j_call']->jsonSerialize();
            }
            if (is_array($row['j_call'])) {
                $row['j_call'] = implode(', or ', $row['j_call']);
            }
            if (! is_string($row['d_call']) && ! is_null($row['d_call'])) {
                $row['d_call'] = $row['d_call']->jsonSerialize();
            }
            if (is_array($row['d_call'])) {
                $row['d_call'] = implode(', or ', $row['d_call']);
            }
            if ($row['functional']) {
                $row['functional'] = true;
            } else {
                $row['functional'] = false;
            }
            if (isset($row['annotation_tool'])) {
                $row['ir_annotation_tool'] = $row['annotation_tool'];
            }
            //remove substring for space/clarity
            if (isset($row['substring'])) {
                unset($row['substring']);
            }
        }

        return $return_array;
    }

    public static function count($f)
    {
        $query = new self();

        self::parseFilter($query, $f);

        return $query->count();
    }

    public static function airr_data($params)
    {
        ini_set('memory_limit', '2G');
        set_time_limit(60 * 60 * 24);
        $start_request = microtime(true);
        $query = new self();
        $airr_headers = FileMapping::createMappingArray("airr", "ir_mongo_database");
        //$filename = $query->getTempFolder() . '/' . uniqid() . '-' . date('Y-m-d_G-i-s', time()) . '.tsv';

        //$file = fopen($filename, 'w');
        $find_options = [];
        $field_to_retrieve = [];
        //foreach (self::$airr_headers as $key=>$value) {
        foreach ($airr_headers as $key=>$value) {
            if ($value != NULL) {
                $field_to_retrieve[$value] = 1;
            }
        }
        $find_options['projection'] = $field_to_retrieve;
        $find_options['projection']['ir_project_sample_id'] = 1;
        $fetch_timeout = $query->getFetchTimeout();
        $find_options['maxTimeMS'] = $fetch_timeout;
        $find_options['noCursorTimeout'] = true;
        $total_time = 0;
        $psa_list = [];
        $sample_id_query = new Sample();
        if (isset($params['ir_project_sample_id_list'])) {
            $sample_id_query = $sample_id_query->whereIn('_id', array_map('intval', $params['ir_project_sample_id_list']));
        }
        $result = $sample_id_query->get();
        $sample_id_list = [];

        foreach ($result as $psa) {
            $psa_list[$psa['_id']] = $psa;
            $sample_id_list[] = $psa['_id'];
        }

        //fputcsv($file, array_keys(self::$airr_headers), chr(9));
        echo implode(array_keys(self::$airr_headers), ',') . "\n";

        $query = new self();
        /*if (isset($params['ir_project_sample_id_list'])) {
            $int_ids = [];

            $query = $query->whereIn('ir_project_sample_id', array_map('intval', $params['ir_project_sample_id_list']));
        }
        self::parseFilter($query, $params);
        $done = false;
        $result = $query->take(5000)->get();*/

        $current = 0;
        foreach ($sample_id_list as $sample_id_current) {
            $sequence_match = self::SequenceMatch($sample_id_current, $params);
            $start = microtime(true);
            try {
                $result = DB::collection($query->getCollection())->raw()->find($sequence_match, $find_options);
            } catch (\Exception $e) {
                Log::error("error in database query \n");
                Log::error($e);

                return -1;
            }
            $time = microtime(true) - $start;
            Log::error("For sample id $sample_id_current query took $time");
            $start = microtime(true);
            try {
                foreach ($result as $row) {
                    $sequence_list = $row;
                    $airr_list = [];
                    //foreach (self::$airr_headers as $airr_name => $ireceptor_name) {
                   
                    foreach ($airr_headers as $airr_name => $ireceptor_name) {
                        if (isset($ireceptor_name) && isset($sequence_list[$ireceptor_name])) {
                            $airr_list[$airr_name] = $sequence_list[$ireceptor_name];
                            if ($airr_name == 'rev_comp') {
                                if ($airr_list['rev_comp'] == '+') {
                                    $airr_list['rev_comp'] = 'true';
                                }
                                if ($airr_list['rev_comp'] == '-') {
                                    $airr_list['rev_comp'] = 'false';
                                }
                            }
                            if ($airr_name == 'productive' || $airr_name == 'functional') {
                                if ($airr_list[$airr_name] == 1) {
                                    $airr_list[$airr_name] = 'true';
                                } elseif ($airr_list[$airr_name] == 0) {
                                    $airr_list[$airr_name] = 'false';
                                }
                            }
                        } else {
                            $airr_list[$airr_name] = '';
                        }
                    }
                    $results_array = [];
                    $sample_array = $psa_list[$sequence_list['ir_project_sample_id']];
                    $results_array = array_merge($airr_list, $sample_array->toArray());

                    $current++;
                    $new_line = [];
                    foreach (array_keys(self::$airr_headers) as $current_header) {
                        if (isset($results_array[$current_header])) {
                            if (is_array($results_array[$current_header])) {
                                $new_line[$current_header] = implode($results_array[$current_header], ', or');
                            } elseif (in_array($current_header, ['v_call', 'd_call', 'j_call']) && $results_array[$current_header] != null && ! is_string($results_array[$current_header])) {
                                $new_line[$current_header] = implode($results_array[$current_header]->jsonSerialize(), ', or ');
                            } else {
                                $new_line[$current_header] = $results_array[$current_header];
                            }
                        } else {
                            $new_line[$current_header] = '';
                        }
                    }
                    //fputcsv($file, $new_line, chr(9));
                    echo implode($new_line, ',') . "\n";

                    //every 5000 results check the free space and fail if empty
                    /*if ($current % 5000 == 0) {
                        $free_space = disk_free_space($query->getTempFolder());
                        if ($free_space == 0) {
                            Log::error('Out of space on device - removing the file');
                            fclose($file);
                            unlink($filename);

                            return -1;
                        }
                    }*/
                }
            } catch (\Exception $e) {
                // fclose($file);
                // unlink($filename);
                Log::error("error in writing \n");
                Log::error($e);

                return -1;
            }
            $time = microtime(true) - $start;
            Log::error("Finished writing line $current took $time");
//            $result = $query->skip($current)->take(5000)->get();
            $total_time = (microtime(true) - $start_request) * 1000;
            if ($total_time > $fetch_timeout && $fetch_timeout > 0) {
                //fclose($file);
                //unlink($filename);
                Log::error("out of time $total_time is greater than $fetch_timeout");

                return -1;
            }
        }
        //fclose($file);
        $time = microtime(true) - $start_request;

        Log::error("Finished creating the file in $time");

        //return $filename;
    }

    public static function data($params)
    {
        ini_set('memory_limit', '1G');

        $query = new self();

        $filename = $query->getTempFolder() . '/' . uniqid() . '-' . date('Y-m-d_G-i-s', time()) . '.csv';

        $file = fopen($filename, 'w');

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
        if (isset($params['ir_project_sample_id_list'])) {
            $int_ids = [];

            $query = $query->whereIn('ir_project_sample_id', array_map('intval', $params['ir_project_sample_id_list']));
        }
        self::parseFilter($query, $params);
        $done = false;
        $result = $query->take(5000)->get();
        $current = 0;
        while ($result->count() > 0) {
            foreach ($result as $row) {
                $sequence_list = $row->toArray();
                $results_array = [];
                $sample_array = $psa_list[$sequence_list['ir_project_sample_id']];
                $results_array = array_merge($sequence_list, $sample_array->toArray());

                $current++;
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

            $result = $query->skip($current)->take(5000)->get();
        }
        fclose($file);

        return $filename;
    }
}
