<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiTest extends TestCase
{
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
}
