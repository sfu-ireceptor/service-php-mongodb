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
        switch (strtolower($params['output'])) {
            case 'csv':
                return Response::download(Sequence::csv($params))->deleteFileAfterSend(true);
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

        $sequence_query_list = Sequence::list($params);
        $t['items'] = $sequence_query_list;

        return json_encode($t);
    }
}
