<?php

namespace App\Http\Controllers;

use App\Info;
use App\Stats;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        $response['result'] = 'success';

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return response($response)->header('Content-Type', 'application/json');
    }

    public function rearrangement_count(Request $request)
    {
        // /rearrangement/count entry point
        //   returns a count of rearrangements per repertoire
        $params = $request->json()->all();
        $response = [];

        $response['Info'] = Info::getIrPlusInfo_stats();

        $error = json_last_error();
        if ($error) {
            //something went bad and Laravel cound't parse the parameters as JSON
            $response['Message'] = 'Unable to parse JSON parameters:' . json_last_error_msg();

            return response($response, 400)->header('Content-Type', 'application/json');
        }

        // try and process the request to get rearrangement count per repertoire
        $l = Stats::statsRequest($params, 'rearrangement_count');
        if ($l == 'error') {
            $response['Message'] = 'Error processing the request';

            return response($response, 400)->header('Content-Type', 'application/json');
        }
        $response['Result'] = $l;
        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        unset($l);

        return response($return_response, 200)->header('Content-Type', 'application/json');
    }

    public function rearrangement_junction_length(Request $request)
    {
        // /rearrangement/junction_length entry point
        //   returns a count of junction lengths per repertoire
        $params = $request->json()->all();
        $response = [];
        $response['Info'] = Info::getIrPlusInfo_stats();

        $error = json_last_error();
        if ($error) {
            //something went bad and Laravel cound't parse the parameters as JSON
            $response['Message'] = 'Unable to parse JSON parameters:' . json_last_error_msg();

            return response($response, 400)->header('Content-Type', 'application/json');
        }
        //process the request
        $l = Stats::statsRequest($params, 'junction_length');
        if ($l == 'error') {
            $response['Message'] = 'Error processing the request';

            return response($response, 400)->header('Content-Type', 'application/json');
        }
        $response['Result'] = $l;
        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return response($return_response, 200)->header('Content-Type', 'application/json');
    }

    public function rearrangement_gene_usage(Request $request)
    {
        // /rearrangement/junction_length entry point
        //   returns a count of junction lengths per repertoire
        $params = $request->json()->all();
        $response = [];
        $response['Info'] = Info::getIrPlusInfo_stats();

        $error = json_last_error();
        if ($error) {
            //something went bad and Laravel cound't parse the parameters as JSON
            $response['Message'] = 'Unable to parse JSON parameters:' . json_last_error_msg();

            return response($response, 400)->header('Content-Type', 'application/json');
        }
        //process the request
        $l = Stats::statsRequest($params, 'gene_usage');
        if ($l == 'error') {
            $response['Message'] = 'Error processing the request';

            return response($response, 400)->header('Content-Type', 'application/json');
        }
        $response['Result'] = $l;
        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return response($return_response, 200)->header('Content-Type', 'application/json');
    }
}
