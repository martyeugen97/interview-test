<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
