<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\BitcoinApiHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    private $supportedMethods = ['rates', 'convert'];
    private $badRequestData = [
        'status' => 'error',
        'code' => 400,
        'message' => 'Bad request'
    ];

    public function index(Request $request)
    {
        $validator = Validator::make(
            [ 'method' => $request->input('method') ],
            [ 'method' => 'required|in:' . implode(',', $this->supportedMethods)]
        );
        if ($validator->fails()) {
                return response()->json($this->badRequestData, 400);
        }
        $method = $request->input('method');
        if (!method_exists($this, $method)) {
            abort(501);
        }
        return call_user_func_array([$this, $method], [$request]);
    }

    private function rates(Request $request)
    {
        if (!$request->isMethod('GET')) {
            return response()->json($this->badRequestData, 400);
        }
        $currency = $request->input('currency');
        $rates = BitcoinApiHelper::bitcoinRates($currency);
        if (!$rates) {
            return response()->json($this->badRequestData, 400);
        }
        $data = [
            'status' => 'success',
            'code' => 200,
            'data' => $rates
        ];
        return response()->json($data, 200);
    }

    private function convert(Request $request)
    {
        if (!$request->isMethod('POST')) {
            return response()->json($this->badRequestData, 400);
        }
        $crypto = ['BTC'];
        $rules = [
            'method' => 'required',
            'value' => 'required|numeric|min:0.01',
            'currency_from' => 'required',
            'currency_to' => 'required'
        ];
        $from = $request->input('currency_from');
        $to = $request->input('currency_to');
        $fromCryptoToFiat = in_array($from, $crypto);
        if ($fromCryptoToFiat) {
            $rules['currency_from'] .= '|in:' . implode(',', $crypto);
            $rules['currency_to'] .= '|not-in:' . implode(',', $crypto);
        } else {
            $rules['currency_to'] .= '|in:' . implode(',', $crypto);
            $rules['currency_from'] .= '|not-in:' . implode(',', $crypto);
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($this->badRequestData, 400);
        }

        $value = (float)$request->input('value');
        $currency = $fromCryptoToFiat ? $to : $from;
        [$convertedValue, $rate] = $fromCryptoToFiat
            ? BitcoinApiHelper::convertToFiat($currency, $value)
            : BitcoinApiHelper::convertToBTC($currency, $value);

        if (!$convertedValue) {
            return response()->json($this->badRequestData, 400);
        }
        return $data = [
                'status' => 'success',
                'code' => 200,
                'data' => [
                    'currency_from' => $from,
                    'currency_to' => $to,
                    'value' => $value,
                    'converted_value' => $convertedValue,
                    'rate' => $rate
                ]
        ];

        return response()->json($data, 200);
    }
}
