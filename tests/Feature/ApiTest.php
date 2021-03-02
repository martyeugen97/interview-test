<?php

namespace Tests\Feature;

use App\Helpers\ArrayHelper;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * Helper function
     * @param array $array
     * @return bool
     */
    private static function isArraySorted(array $array)
    {
        $values = array_values($array);
        for ($i = 0; $i < count($values) - 1; ++$i) {
            if ($values[$i] > $values[$i+1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * API requires a bearer token
     * @test
     * @return void
     */
    public function api_requires_auth()
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/api/v1?method=rates&currency=usd,rub,eur');

        $response->assertForbidden()
            ->assertExactJson([
                'status' => 'error',
                'code' => 403,
                'message' => 'Invalid token'
            ]);
    }

     /**
     * Check if auth works
     * @test
     * @return void
     */
    public function auth_test()
    {
        $url = '/api/v1?method=rates';
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'code' => 200
            ]);
    }

    /**
     * If method doesn't exist, respond with 400 bad request error
     * @test
     * @return void
     */
    public function empty_method()
    {
        $url = '/api/v1';
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'code' => 400,
            ]);
    }

    /**
     * Server returns bitcoin rates and sorts them
     * @test
     * @return void
     */

    public function rates_are_returned_sorted()
    {
        $currencies = ['USD', 'RUB', 'EUR', 'GBP', 'JPY'];
        $url = '/api/v1?method=rates&currency=' . implode(',', $currencies);
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $response->assertOk();
        $rates = $response->json('data');
        $this->assertTrue(ArrayHelper::arrayEqualsIgnoreOrder(array_keys($rates), $currencies));
        $this->assertTrue(self::isArraySorted($rates));
    }

    /**
     * Same works without params
     * @test
     * @return void
     */

    public function rates_works_without_params()
    {
        $url = '/api/v1?method=rates';
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $rates = $response->json('data');
        $this->assertTrue(self::isArraySorted($rates));
    }

    /**
     * Garbage in params shouldn't break the api
     * @test
     * @return void
     */

    public function garbage_in_params_test()
    {
        $url = '/api/v1?method=rates&currency=eoifaihawifawofw';
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $response->assertStatus(400);
    }

    /**
     * Rates must throw 400 if the currency is not supported
     * @test
     * @return void
     */
    public function wrong_currency_error()
    {
        $url = '/api/v1?method=rates&currency=USD,EUR,ETH';
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $response->assertStatus(400);
    }

    /**
     * Testing convert method
     * @test
     * @return void
     */

    public function convert_method()
    {
        $this->withoutExceptionHandling();
        $url = '/api/v1';
        $data = [
            'method' => 'convert',
            'currency_from' => 'BTC',
            'currency_to' => 'USD',
            'value' => 1
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertOk();
    }

    /**
     * Protect index method from being called
     * @test
     * @return void
     */

    public function index_method_should_not_be_allowed()
    {
        $url = '/api/v1?method=index';
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'code' => 400,
            ]);
    }

    /**
     * Checks if we are tryting to convert fiat to fiat
     * @test
     * @return void
     */

    public function fiat_to_fiat_conversions_are_not_supported()
    {
        $this->withoutExceptionHandling();
        $url = '/api/v1';
        $data = [
            'method' => 'convert',
            'currency_from' => 'EUR',
            'currency_to' => 'USD',
            'value' => 1
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertStatus(400);
    }

     /**
     * Checks if we are tryting to convert crypto to crypto
     * @test
     * @return void
     */

    public function crypto_to_crypto_conversions_are_not_supported()
    {
        $this->withoutExceptionHandling();
        $url = '/api/v1';
        $data = [
            'method' => 'convert',
            'currency_from' => 'BTC',
            'currency_to' => 'BTC',
            'value' => 1
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertStatus(400);
    }

    /**
     * Convert value cannot be below 0.01
     * @test
     * @return void
     */

    public function convert_value_too_low()
    {
        $this->withoutExceptionHandling();
        $url = '/api/v1';
        $data = [
            'method' => 'convert',
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'value' => 0.009
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertStatus(400);
    }

    /**
     * Checks if proper http method is used
     * @test
     * @return void
     */

    public function wrong_request_method()
    {
        $url = '/api/v1';
        $data = [
            'method' => 'rates',
            'params' => 'USD,EUR',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertStatus(400);
    }

    /**
     * @test
     * @return void
     */

    public function convert_from_crypto_to_fiat()
    {
        $url = '/api/v1';
        $data = [
            'method' => 'convert',
            'currency_from' => 'BTC',
            'currency_to' => 'USD',
            'value' => 100
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */

    public function convert_fiat_to_crypto()
    {
        $url = '/api/v1';
        $data = [
            'method' => 'convert',
            'currency_from' => 'EUR',
            'currency_to' => 'BTC',
            'value' => 10000
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertStatus(200);
    }

    /**
     * @test convert method must check for bad params
     * @return void
     */
    public function invalid_params_convert()
    {
        $url = '/api/v1';
        $data = [
            'method' => 'convert',
            'currency_from' => 'EUR,RUB',
            'currency_to' => 'BTC',
            'value' => 10000
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->post($url, $data);
        $response->assertStatus(400);
    }
}
