<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        for($i = 0; $i < count($values) - 1; ++$i) {
            if($values[$i] > $values[$i+1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper function that checks if arrays are equal, ignoring order
     * @param array $array1
     * @param array $array2
     * @return bool
     */
    private static function arrayEqualsIgnoreOrder(array $array1, array $array2)
    {
        return !array_diff($array1, $array2) && (count($array1) === count($array2));
    }

    /**
     * API requires a bearer token
     * @test
     * @return void
     */
    public function api_requires_auth()
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/api/v1?method=rates&params=usd,rub,eur');

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
    public function bad_request()
    {
        $url = '/api/v1';
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $response->assertStatus(400)
            ->assertExactJson([
                'status' => 'error',
                'code' => 400,
                'message' => 'Bad request'
            ]);
    }

    /**
     * Server returns bitcoin rates and sorts them
     * @test
     * @return void
     */

    public function rates_are_returned_sorted()
    {
        $currencies = ['usd', 'rub', 'eur', 'gbp', 'jpy'];
        $url = '/api/v1?method=rates&params=' . implode(',', $currencies);
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get($url);
        $rates = $response->json('data');
        $this->assertTrue(self::arrayEqualsIgnoreOrder(array_keys($rates), $currencies));
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
}
