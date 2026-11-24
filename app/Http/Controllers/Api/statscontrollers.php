<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __call($method, $parameters)
    {
        return response()->json(['status' => 'ok']);
    }
}g