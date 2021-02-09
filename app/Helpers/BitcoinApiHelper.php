<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BitcoinApiHelper
{
    const BITCOIN_TICKER_URL = 'https://blockchain.info/ticker';
    const COMISSION_PERCENTAGE = 2;

    public static function getBitcoinBuyRates()
    {
        $response = Http::get(self::BITCOIN_TICKER_URL);
        if(!$response->ok())
            return null;


        $returnData = array();
        foreach($response->json() as $currency => $rate)
        {
            $returnData[$currency] = self::withComission($rate['buy']);
        }

        return $returnData; 
    }

    private static function withComission($value)
    {
        return round($value + ($value * (self::COMISSION_PERCENTAGE/100)), 2);
    }
}