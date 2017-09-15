<?php

namespace App\Http\Controllers;

use App\Sample;
use Illuminate\Http\Request;

class SampleController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->all();

        $l = Sample::list($params);

        return json_encode($l);
    }
}
