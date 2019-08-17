<?php

namespace Tests\Unit\Services;

use App\Contracts\Mailable;
use App\Email;
use App\Mail\Message;
use App\Recipient;
use App\Services\MailjetProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use function factory;

class MailjetProviderTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Provider
     *
     * @var MailjetProvider
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
    private $username = 'test_api_username';

    /**
     * API password
     *
     * @var string
     */
    private $password = 'test_api_password';
        
    /**
     * Name of the provider
     *
     * @var string
     */
    private $providerName = 'mailjet';
    
    
    public function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock('GuzzleHttp\ClientInterface');
        $this->response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $this->client->shouldReceive('request')->andReturn($this->response)->byDefault();
        $this->response->shouldReceive('getStatusCode')->andReturn(200)->byDefault();
        
        $this->provider = new MailjetProvider($this->client, $this->username, $this->password);
        
        $email = factory(Email::class)->create();
        $email->recipients()->saveMany(
            factory(Recipient::class, 4)->make()->all()
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
        $this->expectExceptionMessage('Please provide valid "Username" and "Password" values to access the Mailjet services');
        
        new MailjetProvider($this->client, '', '');
    }
        
    /**
     *
     * @test
     */
    public function addsCredentialsToRequest()
    {
        $apiUsername = $this->username;
        $apiPassword = $this->password;
        
        $this->client->shouldReceive('request')->once()->withArgs(function ($method, $url, $options) use ($apiUsername, $apiPassword) {
            $credentials = $options['auth'];
            
            if ($credentials[0] === $apiUsername && $credentials[1] === $apiPassword) {
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
            
            if ($method == 'POST' && $url == 'https://api.mailjet.com/v3.1/send' && $reqData == $data) {
                return true;
            }
            return false;
        })->andReturn($this->response);
        
        $this->response->shouldReceive('getStatusCode')->andReturn(202);
        
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
        $formattedRecipients = array_map(function ($email) {
            return ['Email' => $email];
        }, $email->getTo());
        
        $format = $email->getFormat() == 'html'? 'HTMLPart': 'TextPart';
        $reqParams = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $email->getFrom()
                    ],
                    'To' => $formattedRecipients,
                    'ReplyTo' => ['Email' => $email->getReplyTo()],
                    'Subject' => $email->getSubject(),
                    $format => $email->getBody(),
                    'CustomID' => "{$email->getMessageId()}"
                ]
            ]
        ];

        return $reqParams;
    }
}
