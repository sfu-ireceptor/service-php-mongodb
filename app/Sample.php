<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Sample extends Model
{
    protected $collection;

    public function __construct()
    {
        if (isset($_ENV['DB_SAMPLES_COLLECTION'])) {
            $this->collection = $_ENV['DB_SAMPLES_COLLECTION'];
        } else {
            $this->collection = 'samples';
        }
    }

    public static function getSamples($f)
    {
        //Log::debug($f);

        $query = new self();

        if (isset($f['ir_lab_id']) && $f['ir_lab_id'] != '') {
            $query = $query->where('ir_lab_id', '=', $f['ir_lab_id']);
        }

        if (isset($f['ir_project_id']) && ! empty($f['ir_project_id'])) {
            $query = $query->whereIn('ir_project_id', $f['ir_project_id']);
        }

        if (isset($f['sex']) && $f['sex'] != '') {
            $query = $query->where('sex', '=', $f['sex']);
        }
        if (isset($f['study_id']) && $f['study_id'] != '') {
            $query = $query->where('study_id', 'like', '%' . $f['study_id'] . '%');
        }

        if (isset($f['study_title']) && $f['study_title'] != '') {
            $query = $query->where('study_title', 'like', '%' . $f['study_title'] . '%');
        }

        if (isset($f['study_description']) && $f['study_description'] != '') {
            $query = $query->where('study_description', 'like', '%' . $f['study_description'] . '%');
        }

        if (isset($f['lab_name']) && $f['lab_name'] != '') {
            $query = $query->where('lab_name', 'like', '%' . $f['lab_name'] . '%');
        }

        if (isset($f['organism']) && $f['organism'] != '') {
            $query = $query->where('organism', 'like', '%' . $f['organism'] . '%');
        }

        if (isset($f['subject_id']) && $f['subject_id'] != '') {
            $query = $query->where('subject_id', 'like', '%' . $f['subject_id'] . '%');
        }

        if (isset($f['ethnicity']) && $f['ethnicity'] != '') {
            $query = $query->whereIn('ethnicity',  $f['ethnicity']);
        }

        if (isset($f['ir_subject_age_min']) && $f['ir_subject_age_min'] != '') {
            $query = $query->where('ir_subject_age_min', '>=', (int)$f['ir_subject_age_min']);
        }

        if (isset($f['ir_subject_age_max']) && $f['ir_subject_age_max'] != '') {
            $query = $query->where('ir_subject_age_max', '<=', (int)$f['ir_subject_age_max']);
        }

        if (isset($f['ir_case_control_id']) && $f['ir_case_control_id'] != '') {
            $query = $query->where('ir_case_control_id', '=', $f['ir_case_control_id']);
        }

        if (isset($f['study_group_description']) && $f['study_group_description'] != '') {
            $query = $query->where('study_group_description', 'like', '%' . $f['study_group_description'] . '%');
        }

        if (isset($f['sample_id']) && $f['sample_id'] != '') {
            $query = $query->where('sample_id', 'like', '%' . $f['sample_id'] . '%');
        }

        if (isset($f['disease_state_sample']) && $f['disease_state_sample'] != '') {
            $query = $query->where('disease_state_sample', 'like', '%' . $f['disease_state_sample'] . '%');
        }

        if (isset($f['cell_phenotype']) && $f['cell_phenotype'] != '') {
            $query = $query->where('cell_phenotype', 'like', '%' . $f['cell_phenotype'] . '%');
        }

        if (isset($f['sequencing_platform']) && $f['sequencing_platform'] != '') {
            $query = $query->where('sequencing_platform', 'like', '%' . $f['sequencing_platform'] . '%');
        }

        if (isset($f['ir_sample_source_id']) && ! empty($f['ir_sample_source_id'])) {
            $query = $query->whereIn('ir_sample_source_id', $f['ir_sample_source_id']);
        }

        if (isset($f['tissue']) && ! empty($f['tissue'])) {
            $query = $query->whereIn('tissue', $f['tissue']);
        }

        if (isset($f['sample_type']) && ! empty($f['sample_type'])) {
            $query = $query->whereIn('sample_type', $f['sample_type']);
        }

        if (isset($f['ir_dna_id']) && ! empty($f['ir_dna_id'])) {
            $query = $query->whereIn('ir_dna_id', $f['ir_dna_id']);
        }

        if (isset($f['template_class']) && ! empty($f['template_class'])) {
            $query = $query->whereIn('template_class', $f['template_class']);
        }

        if (isset($f['dna_type']) && ! empty($f['dna_type'])) {
            $query = $query->whereIn('template_class', $f['dna_type']);
        }

        if (isset($f['cell_subset']) && ! empty($f['cell_subset'])) {
            $query = $query->whereIn('cell_subset', $f['cell_subset']);
        }

        $list = $query->get();

        foreach ($list as $element) {
            $element['ir_project_sample_id'] = $element['_id'];
        }

        return $list;
    }

    public static function list($params)
    {
        $l = static::all();

        return $l;
    }
}
