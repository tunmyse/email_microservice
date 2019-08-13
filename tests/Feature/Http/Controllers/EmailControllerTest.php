<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailControllerTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @test
     * @dataProvider requestDataProvider
     */
    public function testSendEmail($requestData, $statusCode, $status)
    {
        $response = $this->json('POST', '/api/sendemail', $requestData);

        $response
                ->assertStatus($statusCode)
                ->assertJson([
                    'status' => $status,
        ]);
    }

    /**
     * Data provider requests
     */
    public function requestDataProvider()
    {
        return [
            'invalid data' => [
                [
                    'subject' => 1,
                    'body' => 'This is a test transactional email',
                    'recipients' => 'example.test',
                    'format' => 'plain'
                ],
                422,
                'validation_error'
            ],
            'successful request' => [
                [
                    'subject' => 'Test Subject',
                    'body' => 'This is a test transactional email',
                    'recipients' => 'recipient@example.test',
                    'format' => 'html'
                ],
                202,
                'success'
            ]
        ];
    }
}
