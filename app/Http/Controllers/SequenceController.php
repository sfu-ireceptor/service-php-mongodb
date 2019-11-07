<?php

namespace App\Http\Controllers;

use App\Analysis;
use App\CloneData;
use App\Sequence;
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
        if (isset($params['ir_data_format'])) {
            if ($params['ir_data_format'] == 'airr') {
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

                return json_encode($t, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
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

        if ($sequence_summary_list == -1) {
            abort(500, 'Timeout');
        }
        if ($sequence_summary_list == '') {
            $t['items'] = '';
        } else {
            $sequence_query_list = Sequence::list($params, $sequence_summary_list);
            $t['items'] = $sequence_query_list;
        }
        //$t['items'] = Array();
        return json_encode($t, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function data(Request $request)
    {
        $params = $request->all();
        if (isset($params['ir_data_format'])) {
            if ($params['ir_data_format'] == 'airr') {
                //$filename = Sequence::airr_data($params);
                // if ($filename == -1) {
                //  abort(500, 'Timeout');
                //}

                //return response()->download($filename)->deleteFileAfterSend(true);
                return response(Sequence::airr_data($params))->header('Content-Type', 'text/tsv')->header('Content-Disposition', 'attachment;filename="data.tsv"');
            }
        }

        //return response()->download(Sequence::data($params))->deleteFileAfterSend(true);
        abort(500, 'Only AIRR TSV files are supported');
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
