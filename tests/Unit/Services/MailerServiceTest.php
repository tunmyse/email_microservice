<?php

namespace Tests\Unit\Services;

use App\Contracts\Mailable;
use App\Email;
use App\Mail\Message;
use App\Services\MailerService;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;
use function factory;

class MailerServiceTest extends TestCase
{

    /**
     * @var MailerService
     */
    private $mailerService;

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
     * Primary provider name
     *
     * @var string
     */
    private $defaultProviderName = 'defaultprovider';

    /**
     * Email database model
     *
     * @var Email
     */
    private $email;

    /**
     * Mailable generated from email model
     *
     * @var Mailable
     */
    private $mailable;

    public function setUp(): void
    {
        parent::setUp();

        $this->email = factory(Email::class)->make();
        $this->mailable = new Message($this->email->recipients, $this->sender, $this->replyTo, $this->email->subject, $this->email->body, $this->email->format, $this->email->id);
    }

    /**
     *
     * @test
     */
    public function ensuresProvidersImplementMailerProvider()
    {
        $mocks = $this->getMocksForProviders(2, '', false, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All mailer provider services must implement "MailerProvider" interface!');
        $this->mailerService = new MailerService($mocks, $this->sender, $this->replyTo, $this->defaultProviderName);
    }

    /**
     *
     * @test
     */
    public function receivesMinimunNumberOfProviders()
    {
        $mocks = $this->getMocksForProviders(1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MailerService requires at least 2 mailer provider services, 1 provided!');
        $this->mailerService = new MailerService($mocks, $this->sender, $this->replyTo, $this->defaultProviderName);
    }

    /**
     *
     * @test
     */
    public function defaultProviderIsCalledFirst()
    {
        $mocks = $this->getMocksForProviders(3, $this->defaultProviderName);
        $mocks[0]->shouldReceive('sendEmail')->andReturn(true);
        $mocks[1]->shouldNotReceive('sendEmail');
        $mocks[2]->shouldNotReceive('sendEmail');
        
        $mocks[] = array_shift($mocks);

        $this->mailerService = new MailerService($mocks, $this->sender, $this->replyTo, $this->defaultProviderName);

        $this->mailerService->sendEmail($this->email);
    }
        
    /**
     *
     * @test
     */
    public function triesTheNextProviderIfThePreviousFails()
    {
        $mocks = $this->getMocksForProviders(3);
        $this->mailerService = new MailerService($mocks, $this->sender, $this->replyTo, $this->defaultProviderName);
        $this->mailerService->sendEmail($this->email);
    }
    
    /**
     *
     * @test
     */
    public function stopTryingProvidersImmediatelyOneSucceeds()
    {
        $mocks = $this->getMocksForProviders(3);
        $mocks[1]->shouldReceive('sendEmail')->andReturn(true)->ordered();
        $mocks[2]->shouldNotReceive('sendEmail');
        
        $this->mailerService = new MailerService($mocks, $this->sender, $this->replyTo, $this->defaultProviderName);
        $this->mailerService->sendEmail($this->email);
    }
    
    /**
     *
     * @test
     */
    public function callsMailerProviderWithMailable()
    {
        $mailable = $this->mailable;
        $mocks = $this->getMocksForProviders(4);
        
        foreach ($mocks as $mock) {
            $mock->shouldReceive('sendEmail')->withArgs(function (Mailable $email) use ($mailable) {
                if ($email == $mailable) {
                    return true;
                }
                return false;
            })->andReturn(false)->ordered();
        }
        
        $this->mailerService = new MailerService($mocks, $this->sender, $this->replyTo, $this->defaultProviderName);
        $this->mailerService->sendEmail($this->email);
    }
    
    public function getMocksForProviders($count = 2, $defaultProviderName = '', $defaultReturn = false, $includeForeign = false)
    {
        $mocks = [];

        for ($idx = 1; $idx <= $count; $idx++) {
            $providerName = $idx == 1 && !empty($defaultProviderName) ? $defaultProviderName : "{provider{$idx}";
            $mock = Mockery::mock('App\Contracts\MailerProvider');
            $mock->shouldReceive('getProviderName')->andReturn($providerName);
            $mock->shouldReceive('sendEmail')->atMost()->times(1)->andReturn($defaultReturn)->ordered()->byDefault();
            $mocks[] = $mock;
        }

        if ($includeForeign) {
            $mocks[] = Mockery::mock('App\Contracts\Mailable');
        }

        return $mocks;
    }
}
