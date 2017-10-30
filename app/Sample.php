<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Sample extends Model
{
    protected $collection = 'sampleDataNew';

    public static function getSamples($f)
    {
        //Log::debug($f);

        $query = new self();

        if (isset($f['lab_id']) && $f['lab_id'] != '') {
            $query = $query->where('lab_id', '=', (int) $f['lab_id']);
        }

        if (isset($f['project_id']) && ! empty($f['project_id'])) {
            $query = $query->whereIn('project_id', array_map('intval', $f['project_id']));
        }

        if (isset($f['subject_gender']) && $f['subject_gender'] != '') {
            $query = $query->where('subject_gender', '=', $f['subject_gender']);
        }

        if (isset($f['subject_code']) && $f['subject_code'] != '') {
            $query = $query->where('subject_code', 'like', '%' . $f['subject_code'] . '%');
        }

        if (isset($f['subject_ethnicity']) && $f['subject_ethnicity'] != '') {
            $query = $query->where('subject_ethnicity', '=', $f['subject_ethnicity']);
        }

        if (isset($f['subject_age_min']) && $f['subject_age_min'] != '') {
            $query = $query->where('subject_age', '>=', (int) $f['subject_age_min']);
        }

        if (isset($f['subject_age_max']) && $f['subject_age_max'] != '') {
            $query = $query->where('subject_age', '<=', (int) $f['subject_age_max']);
        }

        if (isset($f['case_control_id']) && $f['case_control_id'] != '') {
            $query = $query->where('case_control_id', '=', (int) $f['case_control_id']);
        }

        if (isset($f['case_control_name']) && $f['case_control_name'] != '') {
            $query = $query->where('case_control_name', '=', $f['case_control_name']);
        }

        if (isset($f['sample_name']) && $f['sample_name'] != '') {
            $query = $query->where('sample_name', 'like', '%' . $f['sample_name'] . '%');
        }

        if (isset($f['sample_source_id']) && ! empty($f['sample_source_id'])) {
            $query = $query->whereIn('sample_source_id', array_map('intval', $f['sample_source_id']));
        }

        if (isset($f['sample_source_name']) && ! empty($f['sample_source_name'])) {
            $query = $query->whereIn('sample_source_name', $f['sample_source_name']);
        }

        if (isset($f['dna_id']) && ! empty($f['dna_id'])) {
            $query = $query->whereIn('dna_id', array_map('intval', $f['dna_id']));
        }

        if (isset($f['dna_type']) && ! empty($f['dna_type'])) {
            $query = $query->whereIn('DNA_type', $f['dna_type']);
        }

        if (isset($f['cell_subset']) && ! empty($f['cell_subset'])) {
            $query = $query->whereIn('cell_subset', $f['cell_subset']);
        }

        if (isset($f['ireceptor_cell_subset_name']) && ! empty($f['ireceptor_cell_subset_name'])) {
            $query = $query->whereIn('ireceptor_cell_subset_name', $f['ireceptor_cell_subset_name']);
        }

        $list = $query->get();

	foreach ($list as $element)
	{
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
