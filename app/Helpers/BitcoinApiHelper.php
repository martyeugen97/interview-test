<?php

namespace App\Helpers;

use App\Helpers\ArrayHelper;
use Brick\Math\BigDecimal;
use Brick\Math\BigRational;
use Illuminate\Support\Facades\Http;

class BitcoinApiHelper
{
    const BITCOIN_TICKER_URL = 'https://blockchain.info/ticker';
    const COMISSION_PERCENTAGE = 2;

    public static function bitcoinRates(?String $currency)
    {
        $rates = self::getBitcoinBuyRates();
        if ($currency) {
            $currencies = explode(',', $currency);
            $rates = array_filter(
                $rates,
                function ($currency) use ($currencies) {
                    return in_array($currency, $currencies);
                },
                ARRAY_FILTER_USE_KEY
            );
            if (!ArrayHelper::arrayEqualsIgnoreOrder($currencies, array_keys($rates))) {
                return null;
            }
        }
        asort($rates);
        return $rates;
    }

    public static function convertToBTC(String $currency, String $value)
    {
        $rates = self::getBitcoinBuyRates();
        if (!isset($rates[$currency]) || !is_numeric($rates[$currency])) {
            return [null, null];
        }
        $rate = BigDecimal::of($rates[$currency]);
        $convertedValue = BigRational::of($value)->dividedBy($rate)->toFloat();
        return [number_format($convertedValue, 10), $rate];
    }

    public static function convertToFiat(String $currency, String $value)
    {
        $rates = self::getBitcoinBuyRates();
        if (!isset($rates[$currency]) || !is_numeric($rates[$currency])) {
            return [null, null];
        }
        $rate = BigDecimal::of($rates[$currency]);
        $convertedValue = BigDecimal::of($value)->multipliedBy($rate)->toFloat();
        return [number_format($convertedValue, 2), $rate];
    }

    private static function getBitcoinBuyRates()
    {
        $response = Http::get(self::BITCOIN_TICKER_URL);
        if (!$response->ok()) {
            return null;
        }
        $rates = array();
        foreach ($response->json() as $currency => $rate) {
            $rates[$currency] = self::withComission($rate['buy']);
        }
        return $rates;
    }

    private static function withComission(String $value)
    {
        $valueWithComission = BigDecimal::of($value)
            ->plus(BigDecimal::of($value)->multipliedBy(self::COMISSION_PERCENTAGE/100));
        return (String)round($valueWithComission->toFloat(), 2);
    }
}
