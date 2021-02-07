<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function index()
    {
        $data = [
            'status' => 'error',
            'code' => 403,
            'message' => 'Invalid token'
        ];

        return response()->json($data, 403);
    }

}
