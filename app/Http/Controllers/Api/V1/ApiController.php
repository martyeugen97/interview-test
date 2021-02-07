<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class ApiController extends Controller
{
    const BITCOIN_TICKER_URL = 'https://blockchain.info/ticker';

    private $bitcoin_rates;

    public function index(Request $request)
    {
        $method = $request->input('method');
        if(!method_exists($this, $method))
        {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Bad request'
            ];

            return response()->json($data, 400);
        }

        $response = Http::get(self::BITCOIN_TICKER_URL);
        if(!$response->ok())
            abort(503);

        $this->bitcoin_rates = $response->json();
        if(is_array($this->bitcoin_rates)) {
            return $this->rates(null);
        }
    }

    private function rates($params)
    {
        $data = [
            'status' => 'success',
            'code' => 200,
        ];

        return response()->json($data, 200);
    }
}
