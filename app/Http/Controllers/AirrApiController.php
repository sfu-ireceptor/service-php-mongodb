<?php

namespace App\Http\Controllers;

use App\Sample;
use App\Sequence;
use Illuminate\Http\Request;

class AirrApiController extends Controller
{
    public function index()
    {
        $response['result'] = 'success';

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return response($response)->header("Content-Type", "application/json; charset=utf-8");    }

    public function info()
    {
        $response['name'] = 'airr-api-ireceptor';
        $response['version'] = '0.1.0';

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return response($response)->header("Content-Type", "application/json; charset=utf-8");
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
        $l = Sample::airrRepertoireRequest($params, JSON_OBJECT_AS_ARRAY);
        if ($l == null) {
            $response['succes'] = 'false';
        } else {
            $response['Info']['Title'] = 'AIRR Data Commons API';
            $response['Info']['description'] = 'API response for repertoire query';
            $response['Info']['version'] = 1.3;
            $response['Info']['contact']['name'] = 'AIRR Community';
            $response['Info']['contact']['url'] = 'https://github.com/airr-community';

            if (isset($params['facets'])) {
                //facets have different formatting requirements
                $response['Repertoire'] = Sample::airrRepertoireFacetsResponse($l);
            } else {
                //regular response, needs to be formatted as per AIRR standard, as
                //	iReceptor repertoires are flat collections in MongoDB
                $response['Repertoire'] = Sample::airrRepertoireResponse($l);
            }
        }
        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return response($response)->header("Content-Type", "application/json; charset=utf-8");    }

    public function airr_repertoire_single($repertoire_id)
    {
        $repertoire = Sample::airrRepertoireSingle($repertoire_id);
        $response = Sample::airrRepertoireResponseSingle($repertoire);

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return response($response)->header("Content-Type", "application/json; charset=utf-8");    }

    public function airr_rearrangement(Request $request)
    {
        // /repertoire entry point that resolves an AIRR API rearrangement query request and
        //    currently returns an iReceptor API response
        $params = $request->json()->all();

        if (! isset($params) || empty($params)) {
            //something went bad and Laravel cound't parse the parameters as JSON
            return "{success:'false'}";
        }

        $response = [];
        $l = Sequence::airrRearrangementRequest($params, JSON_OBJECT_AS_ARRAY);
        if ($l == null) {
            $response['succes'] = 'false';
        } else {
            $response['success'] = 'true';
            if (isset($params['facets'])) {
                //facets have different formatting requirements
                $response['result'] = Sequence::airrRearrangementFacetsResponse($l);
            } else {
                //regular response, needs to be formatted as per AIRR standard, as
                //  iReceptor repertoires are flat collections in MongoDB
                $response['result'] = Sequence::airrRearrangementResponse($l);
            }
        }
        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return response($response)->header("Content-Type", "application/json; charset=utf-8");   }

    public function airr_rearrangement_single($rearrangement_id)
    {
        $rearrangement = Sequence::airrRearrangementSingle($rearrangement_id);
        $response = Sequence::airrRearrangementResponseSingle($rearrangement[0]);

        $return_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return response($response)->header("Content-Type", "application/json; charset=utf-8");    }
}
