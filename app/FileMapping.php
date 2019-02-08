<?php

namespace App;

class FileMapping
{
    protected $fileMappings;
    protected $filename;
    protected $rows;

    public function __construct()
    {
        if (isset($_ENV['AIRR_MAPPING_FILE'])) {
            $this->filename = $_ENV['AIRR_MAPPING_FILE'];
        } else {
            $this->filename = '../AIRR-iReceptorMapping.txt';
        }
        $this->fileMappings = [];
        $file = fopen($this->filename, 'r');

        //get headers from the first line
        $headers = [];
        $headers = fgetcsv($file, 0, chr(9));
        $this->rows = 0;
        while ($line = fgetcsv($file, 0, chr(9))) {
            $temp_array = [];
            for ($i = 0; $i < count($line); $i++) {
                $key = $headers[$i];
                $value = $line[$i];
                $temp_array[$key] = $value;
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
    public static function createMappingArray($key, $value)
    {
        $mapping = new self();
        $return_array = [];
        for ($i = 0; $i < $mapping->rows; $i++) {
            $mapping_row = $mapping->fileMappings[$i];
            if (isset($mapping_row[$key])) {
                $return_key = $mapping_row[$key];
                $return_value = $mapping_row[$value];
                $return_array[$return_key] = $return_value;
            }
        }

        return $return_array;
    }
}
