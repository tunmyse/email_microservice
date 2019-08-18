<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendEmailCommandTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @test
     * @dataProvider requestDataProvider
     */
    public function testSendEmail($requestData, $outputs)
    {
        Queue::fake();
        $command = $this->artisan('email:send', $requestData);
        foreach ($outputs as $output) {
            $command->expectsOutput($output);
        }
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
                ['The subject must be a string.', 'The recipients must be an array of valid email addresses.']
            ],
            'single recipient request' => [
                [
                    'subject' => 'Test Subject',
                    'body' => 'This is a test transactional email',
                    'recipients' => 'recipient@example.test',
                    'format' => 'html'
                ],
                ['Your email has been queued and will be sent shortly!']
            ],
            'multiple recipients request' => [
                [
                    'subject' => 'Test Subject',
                    'body' => 'This is a test transactional email',
                    'recipients' => 'recipient@example.test','recipient2@example.test',
                    'format' => 'html'
                ],
                ['Your email has been queued and will be sent shortly!']
            ]
        ];
    }
}
