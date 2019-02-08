<?php

namespace App\Http\Controllers;

use App\FileMapping;
use Illuminate\Http\Request;

class FileMappingController extends Controller
{
    public function index(Request $request)
    {
        $result = FileMapping::createMappingArray('airr', 'ir_mongo_database');
        var_dump($result);
    }
}
