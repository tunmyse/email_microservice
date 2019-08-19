<?php

namespace Tests\Feature\Http\Controllers;

use App\Email;
use App\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use function factory;

class EmailControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider requestDataProvider
     */
    public function testSendEmail($requestData, $statusCode, $status)
    {
        Queue::fake();
        $response = $this->json('POST', '/api/sendemail', $requestData);

        $response
                ->assertStatus($statusCode)
                ->assertJson([
                    'status' => $status,
        ]);
    }

    /**
     * @test
     */
    public function testEmailList()
    {
        $email = factory(Email::class, 2)->create();

        $response = $this->json('GET', '/api/email');

        $response
                ->assertStatus(200)
                ->assertJson([
                    'data' => [
                        [
                            'id' => $email[1]->id,
                            'subject' => $email[1]->subject,
                            'body' => $email[1]->body,
                            'format' => $email[1]->format,
                            'date' => $email[1]->created_at->toJSON()
                        ],
                        [
                            'id' => $email[0]->id,
                            'subject' => $email[0]->subject,
                            'body' => $email[0]->body,
                            'format' => $email[0]->format,
                            'date' => $email[0]->created_at->toJSON()
                        ]
                    ],
        ]);
    }

    /**
     * @test
     */
    public function testEmailRecipient()
    {
        $email = factory(Email::class)->create();
        $email->recipients()->saveMany(
            factory(Recipient::class, 4)->make()->all()
        );
        
        $recipients = [];
        foreach ($email->recipients as $recipient) {
            $recipients[] = ['email' => $recipient->address, 'status' => $recipient->status];
        }
        
        $response = $this->json('GET', '/api/recipient/'.$email->id);
        
        $response->assertStatus(200)
                ->assertJson([
                    'data' => $recipients
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
            'single recipient request' => [
                [
                    'subject' => 'Test Subject',
                    'body' => 'This is a test transactional email',
                    'recipients' => ['recipient@example.test'],
                    'format' => 'html'
                ],
                202,
                'success'
            ],
            'multiple recipients request' => [
                [
                    'subject' => 'Test Subject',
                    'body' => 'This is a test transactional email',
                    'recipients' => ['recipient@example.test', 'recipient2@example.test'],
                    'format' => 'html'
                ],
                202,
                'success'
            ]
        ];
    }
}
