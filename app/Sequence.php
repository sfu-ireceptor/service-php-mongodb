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

    public static function configurableSequenceMatch($id, $f)
    {
        //because we might have different names in repository, we should
        //  apply the mapping of service terms into repository terms, as well
        //  as the mapping of API inputs into service terms

        $repository_names = FileMapping::createMappingArray('service_name', 'ir_mongo_database');
        $filter_names = FileMapping::createMappingArray('service_name', 'ir_api_input');

        $return_match = [];

        //translate all the api parameters that service can use in a filter
        $ir_project_sample_id_repository_name = $repository_names['ir_project_sample_id'];
        $ir_project_sample_id_list_api_name = $filter_names['ir_project_sample_id_list'];
        $functional_api_name = $filter_names['functional'];
        $functional_repository_name = $repository_names['functional'];
        $junction_aa_api_name = $filter_names['junction_aa'];
        $substring_repository_name = $repository_names['substring'];
        $junction_aa_length_api_name = $filter_names['junction_aa_length'];
        $junction_aa_length_repository_name = $repository_names['junction_aa_length'];
        $v_call_api_name = $filter_names['v_call'];
        $v_call_repository_name = $repository_names['v_call'];
        $j_call_api_name = $filter_names['j_call'];
        $j_call_repository_name = $repository_names['j_call'];
        $d_call_api_name = $filter_names['d_call'];
        $d_call_repository_name = $repository_names['d_call'];
        $v_family_repository_name = $repository_names['vgene_family'];
        $d_family_repository_name = $repository_names['dgene_family'];
        $j_family_repository_name = $repository_names['jgene_family'];
        $v_gene_repository_name = $repository_names['vgene_gene'];
        $d_gene_repository_name = $repository_names['dgene_gene'];
        $j_gene_repository_name = $repository_names['jgene_gene'];

        $ir_annotation_tool_api_name = $filter_names['ir_annotation_tool'];
        $ir_annotation_tool_repository_name = $repository_names['ir_annotation_tool'];

        //we process each working sample ID in turn so no need to look at sample id list
        $return_match[$ir_project_sample_id_repository_name] = (int) $id;
        foreach ($f as $filtername => $filtervalue) {

            // map the API terms to repository by going through service terms
            if ($filtername == $ir_project_sample_id_list_api_name) {
                continue;
            }
            if ($filtername == $functional_api_name) {
                $filtervalue = trim($filtervalue);
                if ($filtervalue == 'true') {
                    $return_match[$functional_repository_name] = 1;
                } elseif ($filtervalue == 'false') {
                    $return_match[$functional_repository_name] = 0;
                } else {
                    $return_match[$functional_repository_name] = (int) $filtervalue;
                }

                continue;
            }

            if ($filtername == $junction_aa_api_name) {
                $filtervalue = trim($filtervalue);

                $return_match[$substring_repository_name] = $filtervalue;
                continue;
            }
            if ($filtername == $junction_aa_length_api_name) {
                $filtervalue = trim($filtervalue);
                $return_match[$junction_aa_length_repository_name] = (int) $filtervalue;
                continue;
            }

            if ($filtername == $ir_annotation_tool_api_name) {
                $return_match[$ir_annotation_tool_repository_name] = $filtervalue;
                continue;
            }

            //skip over non-API terms
            if (empty(self::$coltype[$filtername]) || $filtervalue == '') {
                continue;
            }
            if (in_array($filtername, [$v_call_api_name, $j_call_api_name, $d_call_api_name])) {
                $filtervalue = trim($filtervalue);
                preg_match('/(.)_/', $filtername, $gene_prefix);
                $gene_to_filter = $gene_prefix[1];
                if (preg_match("/\*/", $filtervalue)) {
                    $gene_to_filter_service = $gene_to_filter . '_call';
                    $gene_to_filter = $repository_names[$gene_to_filter_service];
                } elseif (preg_match("/\-/", $filtervalue)) {
                    $gene_to_filter_service = $gene_to_filter . 'gene_gene';
                    $gene_to_filter = $repository_names[$gene_to_filter_service];
                } else {
                    $gene_to_filter_service = $gene_to_filter . 'gene_family';
                    $gene_to_filter = $repository_names[$gene_to_filter_service];
                }
                $return_match[$gene_to_filter] = $filtervalue;
                continue;
            }

            //TO DO: replace the column types with mappings
            //  but, these are not filters in API so they should probably be ignored
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
        // we might want to return only functional sequences in the future, but
        // decided not to for now
        //if (! isset($f['functional'])) {
        //$return_match['functional'] = 1;

        //}
        return $return_match;
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
        // we might want to return only functional sequences in the future, but
        // decided not to for now
        //if (! isset($f['functional'])) {
        //$return_match['functional'] = 1;

        //}

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

        // translate repertoire-level terms to api output terms if they are different
        $repo_to_output_sample = FileMapping::createMappingArray('ir_mongo_database', 'ir_api_output', ['ir_class'=>'repertoire']);

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
        $result = $sample_id_query->get()->toArray();
        foreach ($result as $psa) {
            //DB::enableQueryLog();

            //if there's a mapping for any return value, replace it
            foreach ($psa as $psa_name=>$psa_value) {
                // this is baked into mongodb, so doesn't really belong in a mapping file
                if ($psa_name == '_id') {
                    $psa['ir_project_sample_id'] = $psa['_id'];
                    continue;
                }

                //apply mapping if it exists
                if (isset($repo_to_output_sample[$psa_name]) && ($repo_to_output_sample[$psa_name] != '')) {
                    $element[$repo_to_output_sample[$psa_name]] = $psa_value;
                    unset($psa[$psa_name]);
                }
            }
            $total = $psa['ir_sequence_count'];
            if ($has_filter) {
                //$sequence_match = self::SequenceMatch($psa['_id'], $filter);
                $sequence_match = self::configurableSequenceMatch($psa['_id'], $filter);
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

        // map the repository names to API expected output names through service terms
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_mongo_database');
        $return_mapping = FileMapping::createMappingArray('ir_api_output', 'ir_mongo_database', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);

        $num_results = 25;
        $start_at = 0;
        $current_results = 0;
        $result = [];
        $return_array = [];
        $result_array = [];
        foreach ($sample_list as $sample) {
            $needed_results = $num_results - $current_results;
            if ($needed_results < 1) {
                break;
            }
            $sequence_match = self::configurableSequenceMatch($sample['_id'], $f);
            $result = DB::collection($query->getCollection())->raw()->find($sequence_match, ['limit'=>$needed_results]);
            foreach ($result as $sequence) {
                $result_array[] = $sequence;
                $current_results++;
            }
        }
        foreach ($result_array as $row) {
            //no need to map as this is a default MongoDB field
            $row['_id'] = (string) $row['_id'];

            $v_call_repository_name = $repository_names['v_call'];
            $j_call_repository_name = $repository_names['j_call'];
            $d_call_repository_name = $repository_names['d_call'];
            $substring_repository_name = $repository_names['substring'];
            $functional_repository_name = $repository_names['functional'];

            //sometimes v_call, d_call or j_call are Arrays, which require imploding
            if (isset($row[$v_call_repository_name]) && ! is_string($row[$v_call_repository_name]) && ! is_null($row[$v_call_repository_name])) {
                $row[$v_call_repository_name] = $row[$v_call_repository_name]->jsonSerialize();
            }
            if (isset($row[$v_call_repository_name]) && is_array($row[$v_call_repository_name])) {
                $row[$v_call_repository_name] = implode(', or ', $row[$v_call_repository_name]);
            }
            if (isset($row[$j_call_repository_name]) && ! is_string($row[$j_call_repository_name]) && ! is_null($row[$j_call_repository_name])) {
                $row[$j_call_repository_name] = $row[$j_call_repository_name]->jsonSerialize();
            }
            if (isset($row[$j_call_repository_name]) && is_array($row[$j_call_repository_name])) {
                $row[$j_call_repository_name] = implode(', or ', $row[$j_call_repository_name]);
            }
            if (isset($row[$d_call_repository_name]) && ! is_string($row[$d_call_repository_name]) && ! is_null($row[$d_call_repository_name])) {
                $row[$d_call_repository_name] = $row[$d_call_repository_name]->jsonSerialize();
            }
            if (isset($row[$d_call_repository_name]) && is_array($row[$d_call_repository_name])) {
                $row[$d_call_repository_name] = implode(', or ', $row[$d_call_repository_name]);
            }

            //functional might be an int so we convert to boolean
            if (isset($row[$functional_repository_name]) && $row[$functional_repository_name]) {
                $row[$functional_repository_name] = true;
            } else {
                if (isset($row[$functional_repository_name])) {
                    $row[$functional_repository_name] = false;
                } else {
                    $row[$functional_repository_name] = null;
                }
            }
            //remove substring for space/clarity
            if (isset($row[$substring_repository_name])) {
                unset($row[$substring_repository_name]);
            }
            $return_row = [];
            //copy the row to array.
            foreach ($row as $row_key=>$row_value) {
                $return_row[$row_key] = $row_value;
            }
            //map the terms specified in api output column, pass the other values through as-is
            foreach ($return_mapping as $output_name=>$repo_name) {
                $return_row['_id'] = $row['_id'];
                if (isset($repo_name) && isset($row[$repo_name])) {
                    unset($return_row[$repo_name]);
                    $return_row[$output_name] = $row[$repo_name];
                } else {
                    $return_row[$output_name] = null;
                }
            }
            $return_array[] = $return_row;
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
        // We want extra RAM and long time to process the request
        // The time limit is separate from FETCH_QUERY_TIMEOUT in .env and applies
        //  to the Apache HTTP Request duration, whereas FETCH_QUERY_TIMEOUT applies to
        //  the MongoDB timeout value.
        ini_set('memory_limit', '2G');
        set_time_limit(60 * 60 * 24);
        $start_request = microtime(true);
        $query = new self();

        // Create mappings between service terms, database field names and AIRR TSV headers.
        //   as well as which sequence fields we want to fetch (fewer fields make query faster)
        $database_fields = FileMapping::createMappingArray('service_name', 'ir_mongo_database', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        $airr_fields = FileMapping::createMappingArray('airr_tsv', 'service_name');
        $projection_mapping = FileMapping::createMappingArray('ir_mongo_database', 'projection');

        // These are needed for MongoDB query. Here we store max timeout and which fields we want
        //   pulled from databse
        $find_options = [];
        $field_to_retrieve = [];

        // rev_comp and functional field are sometimes stored with annotation values
        //  of + and 1 but AIRR standard requires them to be boolean. Scan the airr to service mapping
        //  for those two values here so we don't have to do it on every sequence.
        // For similar reason, we want a translation of ir_project_sample_id value, which connects
        //  rearrangement with repertoire
        $rev_comp_airr_name = array_search('rev_comp', $airr_fields);
        $functional_arr_name = array_search('functional', $airr_fields);

        //few other variables we use in other arrays, simply to avoid triple-nested array references
        // e.g. $psa_list[$sequence_list[$database_fields['ir_project_sample_id']]];
        $ir_project_sample_id_repository_name = $database_fields['ir_project_sample_id'];
        $v_call_airr_name = array_search('v_call', $airr_fields);
        $j_call_airr_name = array_search('j_call', $airr_fields);
        $d_call_airr_name = array_search('d_call', $airr_fields);

        //create what MongoDB calls 'projection' to retrieve only the fields we use for AIRR TSV
        foreach ($projection_mapping as $key=>$value) {
            if ($value != null) {
                $field_to_retrieve[$key] = 1;
            }
        }

        // Set the MongoDB query options
        $find_options['projection'] = $field_to_retrieve;
        $find_options['projection']['ir_project_sample_id'] = 1;
        $fetch_timeout = $query->getFetchTimeout();
        $find_options['maxTimeMS'] = $fetch_timeout;
        $find_options['noCursorTimeout'] = true;

        // Keep track of total time spent, se we can do timeout on service, even if MongoDB still
        //  has time left
        $total_time = 0;

        // Get the sample (repertoire) information so we can send it alongside each rearrangement
        //  - it may be helpful to analysis apps, but not required by AIRR TSV standard
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

        // Service streams data rather than create temp file than send the file. This is done
        //   to save space on client side, particularly if docker containters are used.
        echo implode(array_keys($airr_fields), chr(9)) . "\n";

        $query = new self();
        $current = 0;

        // Default iReceptor database uses a compound index for sequence searches, so results are
        //   broken up by workign sample even if the inbound filter didn't use it.
        foreach ($sample_id_list as $sample_id_current) {
            $sequence_match = self::configurableSequenceMatch($sample_id_current, $params);
            $start = microtime(true);
            try {
                $result = DB::collection($query->getCollection())->raw()->find($sequence_match, $find_options);
            } catch (\Exception $e) {
                Log::error("error in database query \n");
                Log::error($e);

                abort(500, 'Error in Sequence query.');
            }
            $time = microtime(true) - $start;
            Log::error("For sample id $sample_id_current query took $time");
            $start = microtime(true);
            try {
                foreach ($result as $row) {
                    $sequence_list = $row;
                    $airr_list = [];

                    foreach ($airr_fields as $airr_name => $service_name) {
                        if (isset($service_name) && isset($database_fields[$service_name])) {
                            if (isset($sequence_list[$database_fields[$service_name]])) {
                                $airr_list[$airr_name] = $sequence_list[$database_fields[$service_name]];
                                if ($service_name == 'rev_comp') {
                                    if ($airr_list[$rev_comp_airr_name] == '+') {
                                        $airr_list[$rev_comp_airr_name] = 'true';
                                    }
                                    if ($airr_list[$rev_comp_airr_name] == '-') {
                                        $airr_list[$rev_comp_airr_name] = 'false';
                                    }
                                }
                                if ($service_name == 'functional') {
                                    if ($airr_list[$functional_arr_name] == 1) {
                                        $airr_list[$functional_arr_name] = 'true';
                                    } elseif ($airr_list[$functional_arr_name] == 0) {
                                        $airr_list[$functional_arr_name] = 'false';
                                    }
                                }
                            }
                        } else {
                            $airr_list[$airr_name] = '';
                        }
                    }
                    $results_array = [];
                    $sample_array = $psa_list[$sequence_list[$ir_project_sample_id_repository_name]];
                    $results_array = array_merge($airr_list, $sample_array->toArray());

                    $current++;
                    $new_line = [];
                    foreach (array_keys($airr_fields) as $current_header) {
                        if (isset($results_array[$current_header])) {
                            if (is_array($results_array[$current_header])) {
                                $new_line[$current_header] = implode($results_array[$current_header], ', or');
                            } elseif (in_array($current_header, [$v_call_airr_name, $d_call_airr_name, $j_call_airr_name]) && $results_array[$current_header] != null && ! is_string($results_array[$current_header])) {
                                $new_line[$current_header] = implode($results_array[$current_header]->jsonSerialize(), ', or ');
                            } else {
                                $new_line[$current_header] = $results_array[$current_header];
                            }
                        } else {
                            $new_line[$current_header] = '';
                        }
                    }
                    echo implode($new_line, chr(9)) . "\n";
                }
            } catch (\Exception $e) {
                Log::error("error in writing \n");
                Log::error($e);

                abort(500, 'Error writing sequence response.');
            }
            $time = microtime(true) - $start;
            Log::error("Finished writing line $current took $time");
            $total_time = (microtime(true) - $start_request) * 1000;
            if ($total_time > $fetch_timeout && $fetch_timeout > 0) {
                Log::error("Timeout. Query took $total_time milliseconds and the limit is $fetch_timeout milliseconds");
                abort(500, "Timeout. Query took $total_time milliseconds and the limit is $fetch_timeout milliseconds");
            }
        }
        $time = microtime(true) - $start_request;

        Log::error("Finished creating the file in $time");
    }

    public static function airrRearrangementSingle($rearrangement_id)
    {
        //function that finds a single rearrangement based on the provided $rearrangement_id
        $query = new self();
        $query = $query->where('_id', $rearrangement_id);
        $result = $query->get();

        return $result->toArray();
    }

    public static function airrRearrangementRequest($params)
    {
        //function that processes AIRR API request and returns an array of fields matching
        //   the filters, with optional start number and max number of results
        $repository_names = FileMapping::createMappingArray('service_name', 'ir_mongo_database', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        $airr_names = FileMapping::createMappingArray('airr_full_path', 'ir_mongo_database', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        $airr_to_repository = FileMapping::createMappingArray('airr', 'ir_mongo_database', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        $airr_types = FileMapping::createMappingArray('airr', 'airr_type', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);

        $query_string = '{}';
        $options = [];
        $fields_to_retrieve = [];
        $query = new self();
        // if we have filters, process them
        if (isset($params['filters']) && $params['filters'] != '' && ! empty($params['filters'])) {
            $query_string = AirrUtils::processAirrFilter($params['filters'], $airr_names, $airr_types);
            if ($query_string == null) {
                //something went wrong
                return 'error';
            }
        }
        // if fields parameter is set, we only want to return the fields specified
        if (isset($params['fields']) && $params['fields'] != '') {
            foreach ($params['fields'] as $airr_field_name) {
                if (isset($airr_to_repository[$airr_field_name]) && $airr_to_repository[$airr_field_name] != '') {
                    $fields_to_retrieve[$airr_to_repository[$airr_field_name]] = 1;
                }
            }
            $options['projection'] = $fields_to_retrieve;
        }
        // if we have from parameter, start the query at that value
        if (isset($params['from']) && is_int($params['from'])) {
            $options['skip'] = abs($params['from']);
        }

        // if we have size parameter, don't take more than that number of results
        if (isset($params['size']) && is_int($params['size'])) {
            $options['limit'] = abs($params['size']);
        }

        //echo "<br/>\n Returning $query_string";
        //return ($query_string);

        //if facets is set we want to aggregate by that fields using the sum operation
        if (isset($params['facets']) && $params['facets'] != '') {
            $aggOptions = [];
            $aggOptions[0]['$match'] = json_decode($query_string);
            $aggOptions[1]['$group'] = ['_id'=> [$airr_to_repository[$params['facets']] => '$' . $airr_to_repository[$params['facets']]]];
            $aggOptions[1]['$group']['count'] = ['$sum' => 1];

            $list = DB::collection($query->getCollection())->raw()->aggregate($aggOptions);
        } else {
            $list = DB::collection($query->getCollection())->raw()->find(json_decode($query_string, true), $options);
        }

        //return $list->toArray();
        return $list;
    }

    public static function airrRearrangementResponse($response_list, $response_type)
    {
        //method that takes an array of AIRR terms and returns a JSON string
        //  that represents a repertoire response as defined in AIRR API

        //first, we need some mappings to convert database values to AIRR terms
        //  and bucket them into appropriate AIRR classes
        $db_names = FileMapping::createMappingArray('service_name', 'ir_mongo_database', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        $airr_names = FileMapping::createMappingArray('service_name', 'airr', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        $repository_to_airr = FileMapping::createMappingArray('ir_mongo_database', 'airr', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        //V-, D-, J-call might be stored as an array, which need to be serialized before they can be outputted in TSV format
        $v_call_airr_name = array_search('v_call', $airr_names);
        $j_call_airr_name = array_search('j_call', $airr_names);
        $d_call_airr_name = array_search('d_call', $airr_names);

        //each iReceptor 'sample' is an AIRR repertoire consisting of a single sample and  a single rearrangement set
        //  associated with it, so we will take the array of samples and place each element into an appropriate section
        //  of AIRR reperotoire response

        $headers = true;
        if ($response_type == 'json') {
            header('Content-Type: application/json; charset=utf-8');
            echo '{Info:';
            $response['Title'] = 'AIRR Data Commons API';
            $response['description'] = 'API response for repertoire query';
            $response['version'] = 1.3;
            $response['contact']['name'] = 'AIRR Community';
            $response['contact']['url'] = 'https://github.com/airr-community';
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            echo ", Rearrangement:[\n";
        }
        if ($response_type == 'tsv') {
            header('Content-Type: text/tsv; charset=utf-8');
            header('Content-Disposition: attachment;filename="data.tsv"');
        }
        foreach ($response_list as $repertoire) {
            $return_array = [];
            foreach ($repertoire as $return_key => $return_element) {
                if (isset($repository_to_airr[$return_key]) && $repository_to_airr[$return_key] != '') {
                    array_set($return_array, $repository_to_airr[$return_key], $return_element);

                    if ($response_type == 'tsv') {
                        // mongodb BSON array needs to be serialized or it can't be used in TSV output
                        if (in_array($return_key, [$v_call_airr_name, $d_call_airr_name, $j_call_airr_name])
                            && $return_element != null && ! is_string($return_element)) {
                            $return_array[$repository_to_airr[$return_key]] = implode($return_element->jsonSerialize(), ', or ');
                        }
                    }
                }
            }
            // first time through, if we have tsv, dump the return array's keys as headers
            if ($headers && $response_type == 'tsv') {
                echo implode(array_keys($return_array), chr(9)) . "\n";
                $headers = false;
            }
            if ($response_type == 'tsv') {
                echo implode($return_array, chr(9)) . "\n";
            } else {
                echo json_encode($return_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
        }
        if ($response_type == 'json') {
            echo "]}\n";
        }
    }

    public static function airrRearrangementFacetsResponse($response_list)
    {
        $return_array = [];
        $response_mapping = FileMapping::createMappingArray('ir_mongo_database', 'airr', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);

        //MongoDB by default aggregates in the format _id: {column: value}, count: sum
        //  AIRR expects {column: value, count: sum} {column: value2, count: sum}
        foreach ($response_list as $response) {
            $temp = [];
            $facet = $response['_id'];
            $count = $response['count'];
            $facet_name = $response_mapping[key($facet)];
            $temp[$facet_name] = $facet[key($facet)];
            $temp['count'] = $count;
            $return_array[] = $temp;
        }

        return $return_array;
    }

    public static function airrRearrangementResponseSingle($rearrangement)
    {
        //take a single rearrangement from database query and create a response as per
        //  AIRR API standard
        $result = [];
        $response_mapping = FileMapping::createMappingArray('ir_mongo_database', 'airr', ['ir_class'=>['rearrangement', 'ir_rearrangement']]);
        foreach ($rearrangement as $key=>$value) {
            if (isset($response_mapping[$key]) && $response_mapping[$key] != '') {
                $result[$response_mapping[$key]] = $value;
            }
        }

        return $result;
    }
}
