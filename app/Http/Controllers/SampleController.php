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
}
