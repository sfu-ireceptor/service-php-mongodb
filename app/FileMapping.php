<?php

namespace App;

class FileMapping
{
    protected $fileMappings;
    protected $filename;
    protected $rows;
    protected $separator;

    public function __construct()
    {
    	$this->separator = chr(9);
        if (isset($_ENV['AIRR_MAPPING_FILE'])) {
            $this->filename = $_ENV['AIRR_MAPPING_FILE'];
        } else {
            $this->filename = '../AIRR-iReceptorMapping.txt';
        }
        $this->fileMappings = [];
        $file = fopen($this->filename, 'r');

        //get headers from the first line
        $headers = [];
        $headers = fgetcsv($file, 0, $this->separator);
        $this->rows = 0;
        while ($line = fgetcsv($file, 0, $this->separator)) {
            $temp_array = [];
            for ($i = 0; $i < count($line); $i++) {
                $key = $headers[$i];
                $value = $line[$i];
                if (isset($value) && $value != '') {
                    $temp_array[$key] = $value;
                } else {
                    $temp_array[$key] = null;
                }
            }
            array_push($this->fileMappings, $temp_array);

            $this->rows++;
        }
    }

    public static function printMappings()
    {
        $mapping = new self();
        for ($i = 0; $i < $mapping->rows; $i++) {
            $mapping_row = $mapping->fileMappings[$i];
            //var_dump($mapping_row);
            foreach ($mapping_row as $key=>$value) {
                echo "$key\t$value<br/>\n";
            }
        }
    }

    //function to create an array of mappings between $key and $value
    //  provided by the user
    //    e.g. createMappingArray("ir_curator", "airr") will create an array in
    //	    which curator terms are a key and corresponding airr terms are a value
    //   $condition is an array of key/value pairs that define which rows will be considered
    //    e.g. createMappingArray("ir_curator", "airr", ["ir_class"=>"repertoire"}]) will only
    //      map an ir_curator term to an airr term if the ir_class of that row is "repertoire"
    public static function createMappingArray($key, $value, $condition_array = null)
    {
        $mapping = new self();
        $return_array = [];
        $has_condition = ($condition_array != null && is_array($condition_array));
        for ($i = 0; $i < $mapping->rows; $i++) {
            $mapping_row = $mapping->fileMappings[$i];

            //check if there's a mapping condition and if so, does the row pass it
            $skip_row = false;
            if ($has_condition) {
                foreach ($condition_array as $condition_name=>$condition_value) {
                    if (isset($mapping_row[$condition_name])) {
                        // we have the row with condition_name in it, let's see if passes the filter
                        if (is_array($condition_value)) {
                            if (! in_array($mapping_row[$condition_name], $condition_value)) {
                                $skip_row = true;
                            }
                        } else {
                            if ($mapping_row[$condition_name] != $condition_value) {
                                $skip_row = true;
                            }
                        }
                    } else {
                        $skip_row = true;
                    }
                }
            }
            if ($skip_row) {
                continue;
            }

            if (isset($mapping_row[$key]) && ($mapping_row[$key] != '')) {
                $return_key = $mapping_row[$key];
                $return_value = $mapping_row[$value];
                $return_array[$return_key] = $return_value;
            }
        }

        return $return_array;
    }
}
