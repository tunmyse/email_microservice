<?php

namespace Tests\Unit\Services;

use App\Jobs\EmailJob;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var EmailService
     */
    private $emailService;

    public function setUp(): void
    {
        parent::setUp();
        $this->emailService = new EmailService();
    }

    /**
     * @test
     * @dataProvider invalidDataProvider
     */
    public function validationFailsForInvalidData($invalidData)
    {
        $response = $this->emailService->enqueueEmail($invalidData);
        $this->assertIsArray($response);
        $this->assertSame('validation_error', $response['status']);
    }

    /**
     * @test
     */
    public function savesEmailToDatabase()
    {
        $emailData = [
            'subject' => 'Test Subject',
            'body' => 'This is a test transactional email',
            'recipients' => 'recipient@example.test',
            'format' => 'plain'
        ];

        $this->emailService->enqueueEmail($emailData);
        
        $this->assertDatabaseHas('emails', [
            'subject' => $emailData['subject'],
            'body' => $emailData['body'],
            'recipients' => $emailData['recipients'],
            'format' => $emailData['format']
        ]);
    }

    /**
     * @test
     */
    public function addsEmailJobToQueue()
    {
        $emailData = [
            'subject' => 'Test Subject',
            'body' => 'This is a test transactional email',
            'recipients' => 'recipient@example.test',
            'format' => 'plain'
        ];
                
        Queue::fake();
        
        $this->emailService->enqueueEmail($emailData);
        Queue::assertPushed(EmailJob::class);
    }
    
    /**
     * Data provider for invalid email data
     */
    public function invalidDataProvider()
    {
        return [
            'missing required fields' => [
                [
                    'subject' => 'Test Subject',
                    'body' => 'This is a test transaction email!'
                ]
            ],
            'invalid data type' => [
                [
                    'subject' => 1,
                    'body' => 'This is a test transactional email',
                    'recipient' => 'recipient@example.test',
                    'format' => 'plain'
                ]
            ],
            'wrong format type' => [
                [
                    'subject' => 'Test Subject',
                    'body' => 'This is a test transactional email',
                    'recipient' => 'recipient@example.test',
                    'format' => 'json'
                ]
            ]
        ];
    }
}
