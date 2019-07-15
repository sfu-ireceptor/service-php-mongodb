<?php

namespace App\Http\Controllers;

use App\Sample;
use Illuminate\Http\Request;

class SampleController extends Controller
{
    // /samples entry point that resolves an iReceptor API request and returns
    //   iReceptor API response
    public function index(Request $request)
    {
        $params = $request->all();

        //$l = Sample::list($params);
        $l = Sample::getSamples($params);

        return json_encode($l, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function airr_repertoire(Request $request)
    {
        // /repertoire entry point that resolves an AIRR API repertoire query request and
        //    currently returns an iReceptor API response
        $params = $request->json()->all();

        if (! isset($params) || empty($params)) {
            //something went bad and Laravel cound't parse the parameters as JSON
            return "{success:'false'}";
        }

        $response = [];
        $l = Sample::airrRepertoireRequest($params, JSON_OBJECT_AS_ARRAY);
        if ($l == null) {
            $response['succes'] = 'false';
        } else {
            $response['success'] = 'true';
            if (isset($params['facets'])) {
                //facets have different formatting requirements
                $response['result'] = Sample::airrRepertoireFacetsResponse($l);
            } else {
                //regular response, needs to be formatted as per AIRR standard, as
                //	iReceptor repertoires are flat collections in MongoDB
                $response['result'] = Sample::airrRepertoireResponse($l);
            }
        }
        //return($response);
        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
    public function airr_repertoire_single($repertoire_id)
    {
        $repertoire = Sample::airrRepertoireSingle($repertoire_id);
        $response = Sample::airrRepertoireResponse($repertoire);
        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
