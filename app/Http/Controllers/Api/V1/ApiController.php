<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\BitcoinApiHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    private $supported_methods = ['rates', 'convert'];

    public function index(Request $request)
    {
        $method = $request->input('method');
        if(!method_exists($this, $method) || !in_array($method, $this->supported_methods))
        {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Bad request'
            ];
            
            return response()->json($data, 400);
        }
        
        $rates = BitcoinApiHelper::getBitcoinBuyRates();
        if($rates)
        {
            return call_user_func_array([$this, $method], [$rates, $request->input('params')]);
        }
    }

    private function rates($rates, $params)
    {
        if($params)
        {
            $responseCurrencies = explode(',', $params);
            $rates = array_filter($rates, function($currency) use ($responseCurrencies) {
                return in_array($currency, $responseCurrencies);
            }, ARRAY_FILTER_USE_KEY);
        }

        asort($rates);
        $data = [
            'status' => 'success',
            'code' => 200,
            'data' => $rates
        ];

        return response()->json($data, 200);
    }

    private function convert($rates)
    {

    }
}
