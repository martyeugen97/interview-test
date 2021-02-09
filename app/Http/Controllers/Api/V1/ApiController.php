<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\BitcoinApiHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use function PHPUnit\Framework\returnValue;

class ApiController extends Controller
{
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
        
        $rates = BitcoinApiHelper::getBitcoinBuyRates();
        if($rates)
        {
            return $this->rates($rates, $request->input('params'));
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
}
