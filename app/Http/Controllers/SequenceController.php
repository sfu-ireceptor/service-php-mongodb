<?php

namespace App\Http\Controllers;

use App\Analysis;
use App\Sequence;
use App\CloneData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SequenceController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->all();
        $t = [];

        if (! isset($params['output'])) {
            $params['output'] = 'json';
        }
        if (isset ($params['ir_data_format']))
        {
            if ($params['ir_data_format'] == 'airr')
            {
                return response()->download(Sequence::airr_data($params))->deleteFileAfterSend(true);
            }
        }
        switch (strtolower($params['output'])) {
            case 'csv':
                return response()->download(Sequence::data($params))->deleteFileAfterSend(true);
                break;
            default:
                $t['items'] = Sequence::list($params);
                $t['total'] = Sequence::count($params);
                return json_encode($t);
        }
    }

    public function clones(Request $request)
    {
        $params = $request->all();

        $t = [];
        if (empty($params['output']) || ($params['output'] != 'csv')) {
            $t['items'] = [];
            $t['total'] = 0;

            return json_encode($t);
        } else {
            // return Response::download(CloneData::csv($params));
        }
    }

    public function analysis(Request $request)
    {
        $params = $request->all();

        $analysis_list = Analysis::list($params);

        return json_encode($analysis_list);
    }

    public function summary(Request $request)
    {
        $params = $request->all();

        $t = [];
        $sequence_summary_list = Sequence::aggregate($params);
        $t['summary'] = $sequence_summary_list;

        $sequence_query_list = Sequence::list($params, $sequence_summary_list);
        $t['items'] = $sequence_query_list;
        //$t['items'] = Array();
        return json_encode($t);
    }

    public function data(Request $request)
    {
        $params = $request->all();
        if (isset ($params['ir_data_format']))
        {
            if ($params['ir_data_format'] == 'airr')
            {
                return response()->download(Sequence::airr_data($params))->deleteFileAfterSend(true);
            }
        }

        return response()->download(Sequence::data($params))->deleteFileAfterSend(true);
    }

    public function v1controls(Request $request)
    {
        $params = $request->all();

        if (isset($params['output'])) {
            if ($params['output'] == 'csv') {
                return response()->download(Sequence::data($params))->deleteFileAfterSend(true);
            }
        }
    }
}
