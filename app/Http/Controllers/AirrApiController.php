<?php

namespace App\Http\Controllers;

use App\AirrUtils;
use App\Sample;
use App\Sequence;
use Illuminate\Http\Request;

class AirrApiController extends Controller
{
    public function index()
    {
        $response['result'] = 'success';

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return response($response)->header('Content-Type', 'application/json');
    }

    public function info()
    {
        $response['name'] = 'airr-api-ireceptor';
        $response['version'] = '0.1.0';

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return response($response)->header('Content-Type', 'application/json');
    }

    public function swagger()
    {
    }

    public function airr_repertoire(Request $request)
    {
        // /repertoire entry point that resolves an AIRR API repertoire query request and
        //    currently returns an iReceptor API response
        $params = $request->json()->all();
        $response = [];
        //if (! isset($params) ) {
        $error = json_last_error();
        if ($error) {
            //something went bad and Laravel cound't parse the parameters as JSON
            $response['message'] = 'Unable to parse JSON parameters:' . json_last_error_msg();

            return response($response, 400)->header('Content-Type', 'application/json');
        }
        $l = Sample::airrRepertoireRequest($params, JSON_OBJECT_AS_ARRAY);
        if ($l == 'error') {
            $response['message'] = 'Unable to parse the filter.';

            return response($response, 400)->header('Content-Type', 'application/json');
        } else {
            $response['Info']['Title'] = 'AIRR Data Commons API';
            $response['Info']['description'] = 'API response for repertoire query';
            $response['Info']['version'] = 1.3;
            $response['Info']['contact']['name'] = 'AIRR Community';
            $response['Info']['contact']['url'] = 'https://github.com/airr-community';

            if (isset($params['facets'])) {
                //facets have different formatting requirements
                $response['Facet'] = Sample::airrRepertoireFacetsResponse($l);
            } else {
                //regular response, needs to be formatted as per AIRR standard, as
                //	iReceptor repertoires are flat collections in MongoDB
                $response['Repertoire'] = Sample::airrRepertoireResponse($l);
            }
        }
        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return response($response)->header('Content-Type', 'application/json');
    }

    public function airr_repertoire_single($repertoire_id)
    {
        $repertoire = Sample::airrRepertoireSingle($repertoire_id);
        $response['Info']['Title'] = 'AIRR Data Commons API';
        $response['Info']['description'] = 'API response for repertoire query';
        $response['Info']['version'] = 1.3;
        $response['Info']['contact']['name'] = 'AIRR Community';
        $response['Info']['contact']['url'] = 'https://github.com/airr-community';
        $response['Repertoire'] = Sample::airrRepertoireResponseSingle($repertoire);

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return response($response)->header('Content-Type', 'application/json');
    }

    public function airr_rearrangement(Request $request)
    {
        // /repertoire entry point that resolves an AIRR API rearrangement query request and
        //    currently returns an iReceptor API response
        $params = $request->json()->all();

        $error = json_last_error();
        if ($error) {
            //something went bad and Laravel cound't parse the parameters as JSON
            $response['message'] = 'Unable to parse JSON parameters:' . json_last_error_msg();

            return response($response, 400)->header('Content-Type', 'application/json');
        }

        $response = [];
        //check if we can optimize the ADC API query for our repository
        //  if so, go down optimizied query path
        if (AirrUtils::queryOptimizable($params, JSON_OBJECT_AS_ARRAY)) {
            return response()->streamDownload(function () use ($params) {
                Sequence::airrOptimizedRearrangementRequest($params, JSON_OBJECT_AS_ARRAY);
            });
        } else {
            $l = Sequence::airrRearrangementRequest($params, JSON_OBJECT_AS_ARRAY);

            if ($l == 'error') {
                $response['message'] = 'Unable to parse the filter.';
                $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

                return response($response, 400)->header('Content-Type', 'application/json');
            } else {
                //check what kind of response we have, default to JSON
                $response_type = 'json';
                if (isset($params['format']) && $params['format'] != '') {
                    $response_type = strtolower($params['format']);
                }
                if (isset($params['facets'])) {
                    //facets have different formatting requirements
                    $response['Info']['Title'] = 'AIRR Data Commons API';
                    $response['Info']['description'] = 'API response for repertoire query';
                    $response['Info']['version'] = 1.3;
                    $response['Info']['contact']['name'] = 'AIRR Community';
                    $response['Info']['contact']['url'] = 'https://github.com/airr-community';
                    $response['Facet'] = Sequence::airrRearrangementFacetsResponse($l);

                    return response($response)->header('Content-Type', 'application/json');
                } else {
                    //regular response, needs to be formatted as per AIRR standard, as
                    //  iReceptor repertoires are flat collections in MongoDB
                    //$response['result'] = Sequence::airrRearrangementResponse($l);
                    return response()->streamDownload(function () use ($l, $response_type) {
                        Sequence::airrRearrangementResponse($l, $response_type);
                    });
                }
            }
        }
    }

    public function airr_rearrangement_single($rearrangement_id)
    {
        $rearrangement = Sequence::airrRearrangementSingle($rearrangement_id);
        $response['Info']['Title'] = 'AIRR Data Commons API';
        $response['Info']['description'] = 'API response for repertoire query';
        $response['Info']['version'] = 1.3;
        $response['Info']['contact']['name'] = 'AIRR Community';
        $response['Info']['contact']['url'] = 'https://github.com/airr-community';
        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $response['Rearrangement'] = Sequence::airrRearrangementResponseSingle($rearrangement[0]);

        return response($response)->header('Content-Type', 'application/json');
    }
}
