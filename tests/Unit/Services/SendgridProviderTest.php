<?php

namespace Tests\Unit\Services;

use App\Contracts\Mailable;
use App\Email;
use App\Mail\Message;
use App\Services\SendgridProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
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
        $this->mailable = new Message($email->recipients, $this->sender, $this->replyTo, $email->subject, $email->body, $email->format, $email->id);
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
        
        $this->client->shouldReceive('request')->withArgs(function ($method, $url, $options) use ($apiToken) {
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
        
        $this->client->shouldReceive('request')->atLeast()->times(1)->withArgs(function ($method, $url, $options) use ($data) {
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
    
    public function getRequestDataFromMailable(Mailable $email)
    {
        $reqParams = [
            'personalizations' => [
                'to' => [
                    'email' => $email->getTo()
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
                'type' => 'text/'.$email->getFormat(),
                'value' => $email->getBody()
            ],
            'custom_args' => ['app_message_id' => $email->getMessageId()]
        ];
        
        return $reqParams;
    }
}
