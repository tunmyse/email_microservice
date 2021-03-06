<?php

namespace Tests\Unit\Services;

use App\Contracts\Mailable;
use App\Email;
use App\Mail\DefaultEmailEvent;
use App\Mail\Message;
use App\Recipient;
use App\Services\SendgridProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Iterator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use function factory;

class SendgridProviderTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Provider
     *
     * @var SendgridProvider
     */
    private $provider;
    
    /**
     * Http client
     *
     * @var MockInterface
     */
    private $client;

    /**
     * Api call response
     *
     * @var MockInterface
     */
    private $response;
    
    /**
     * Mailable generated from email model
     *
     * @var Mailable
     */
    private $mailable;
    
    /**
     * Sender email
     *
     * @var string
     */
    private $sender = 'sender@example.com';

    /**
     * Reply email
     *
     * @var string
     */
    private $replyTo = 'reply@example.com';
    
    /**
     * API username
     *
     * @var string
     */
    private $token = 'test_api_token';
    
    /**
     * Name of the provider
     *
     * @var string
     */
    private $providerName = 'sendgrid';
    
    
    public function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock('GuzzleHttp\ClientInterface');
        $this->response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $this->client->shouldReceive('request')->andReturn($this->response)->byDefault();
        $this->response->shouldReceive('getStatusCode')->andReturn(202)->byDefault();
        
        $this->provider = new SendgridProvider($this->client, $this->token);
        
        $email = factory(Email::class)->create();
        $email->recipients()->saveMany(
            factory(Recipient::class, 3)->make()->all()
        );

        $recipients = $email->recipients()->pluck('address')->all();
        $this->mailable = new Message($recipients, $this->sender, $this->replyTo, $email->subject, $email->body, $email->format, $email->id);
    }

    /**
     *
     * @test
     */
    public function returnsCorrectProviderName()
    {
        $this->assertEquals($this->providerName, $this->provider->getProviderName());
    }
    
    /**
     *
     * @test
     */
    public function validateApiCredentials()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide a valid "API Token" value to access the Sendgrid services!');
        
        new SendgridProvider($this->client, '', '');
    }
        
    /**
     *
     * @test
     */
    public function addsCredentialsToRequest()
    {
        $apiToken = "Bearer {$this->token}";
        
        $this->client->shouldReceive('request')->once()->withArgs(function ($method, $url, $options) use ($apiToken) {
            $credentials = $options['headers']['Authorization'];
            
            if ($credentials === $apiToken) {
                return true;
            }
            return false;
        })->andReturn($this->response);
                
        $this->provider->sendEmail($this->mailable);
    }
    
    /**
     *
     * @test
     */
    public function makesCorrectRequestToApi()
    {
        $data = $this->getRequestDataFromMailable($this->mailable);
        
        $this->client->shouldReceive('request')->once()->withArgs(function ($method, $url, $options) use ($data) {
            $reqData = $options['json'];
            
            if ($method == 'POST' && $url == 'https://api.sendgrid.com/v3/mail/send' && $reqData == $data) {
                return true;
            }
            return false;
        })->andReturn($this->response);
        
        $this->response->shouldReceive('getStatusCode')->andReturn(200);
        
        $this->provider->sendEmail($this->mailable);
    }
        
    /**
     *
     * @test
     */
    public function shouldReturnTrueForSuccessfulStatus()
    {
        $this->assertTrue($this->provider->sendEmail($this->mailable));
    }
    
    /**
     *
     * @test
     */
    public function shouldReturnFalseForFailStatus()
    {
        $this->response->shouldReceive('getStatusCode')->andReturn(500);
        
        $this->assertFalse($this->provider->sendEmail($this->mailable));
    }
    
    
    /**
     *
     * @test
     */
    public function returnsIteratorForMailEvents()
    {
        $this->assertInstanceOf(Iterator::class, $this->provider->processMailEvents([]));
    }
    
    /**
     *
     * @test
     */
    public function returnsValidMailEventsObjects()
    {
        $sampleEvents = [
            [
                'attempt' => '2',
                'email' => 'test@example.com',
                'event' => 'deferred',
                'timestamp' => 1566153660,
                'tls' => 1,
                'app_message_id' => '4'
            ],
            [
                'attempt' => '0',
                'email' => 'test2@example.com',
                'event' => 'deferred',
                'timestamp' => 1566153660,
                'tls' => 1
            ]
        ];

        $iterator = $this->provider->processMailEvents($sampleEvents);

        $count = 0;
        $mailEvents = [];
        
        foreach ($iterator as $event) {
            $mailEvents[] = $event;
            $count++;
        }
        
        $validEvent = $sampleEvents[0];
        $this->assertEquals(1, $count);
        
        $processedEvent = new DefaultEmailEvent($validEvent['email'], $validEvent['event'], $validEvent['app_message_id'], 'sendgrid');
        $this->assertEquals($mailEvents[0], $processedEvent);
    }
    
    public function getRequestDataFromMailable(Mailable $email)
    {
        $formattedRecipients = array_map(function ($email) {
            return ['email' => $email];
        }, $email->getTo());
        
        $reqParams = [
            'personalizations' => [
                [
                    'to' => $formattedRecipients
                ]
            ],
            'from' => [
                'email' => $email->getFrom()
            ],
            'reply_to' => [
                'email' => $email->getReplyTo()
            ],
            'subject' => $email->getSubject(),
            'content' => [
                [
                    'type' => 'text/' . $email->getFormat(),
                    'value' => $email->getBody()
                ]
            ],
            'custom_args' => ['app_message_id' => $email->getMessageId()]
        ];

        return $reqParams;
    }
}
