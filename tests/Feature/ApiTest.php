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
        $response = $this->withHeader('Authorization', 'Bearer ' . env('API_TOKEN'))->get('/api/v1?method=rates&params=usd,rub,eur');
        $response->assertOk();
    }
}
