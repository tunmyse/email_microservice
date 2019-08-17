<?php

namespace Tests\Unit\Services;

use App\Contracts\Mailable;
use App\Email;
use App\Mail\Message;
use App\Recipient;
use App\Services\MailerService;
use Illuminate\Container\RewindableGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;
use function count;
use function factory;

class MailerServiceTest extends TestCase
{
    use RefreshDatabase;
    
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

    public function setUp(): void
    {
        parent::setUp();

        $this->email = factory(Email::class)->create();
        $this->email->recipients()->saveMany(
            factory(Recipient::class, 4)->make()->all()
             );
    }

    /**
     *
     * @test
     */
    public function ensuresProvidersImplementMailerProvider()
    {
        $mocks = $this->getMocksForProviders(2, '', false, true);
        $generator = $this->getMockGenerator($mocks);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All mailer provider services must implement "MailerProvider" interface!');
        
        new MailerService($generator, $this->sender, $this->replyTo, $this->defaultProviderName);
    }

    /**
     *
     * @test
     */
    public function receivesMinimunNumberOfProviders()
    {
        $mocks = $this->getMocksForProviders(1);
        $generator = $this->getMockGenerator($mocks);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MailerService requires at least 2 mailer provider services, 1 provided!');
        new MailerService($generator, $this->sender, $this->replyTo, $this->defaultProviderName);
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
        $generator = $this->getMockGenerator($mocks);
        
        $mailerService = new MailerService($generator, $this->sender, $this->replyTo, $this->defaultProviderName);
        $mailerService->sendEmail($this->email);
    }
        
    /**
     *
     * @test
     */
    public function triesTheNextProviderIfThePreviousFails()
    {
        $mocks = $this->getMocksForProviders(3);
        $generator = $this->getMockGenerator($mocks);
        $mailerService = new MailerService($generator, $this->sender, $this->replyTo, $this->defaultProviderName);
        $mailerService->sendEmail($this->email);
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
        $generator = $this->getMockGenerator($mocks);
        
        $mailerService = new MailerService($generator, $this->sender, $this->replyTo, $this->defaultProviderName);
        $mailerService->sendEmail($this->email);
    }
    
    /**
     *
     * @test
     */
    public function callsMailerProviderWithMailable()
    {
        $recipients = $this->email->recipients()->pluck('address')->all();
        $mailable = new Message($recipients, $this->sender, $this->replyTo, $this->email->subject, $this->email->body, $this->email->format, $this->email->id);
        $mocks = $this->getMocksForProviders(4);
        
        foreach ($mocks as $mock) {
            $mock->shouldReceive('sendEmail')->withArgs(function (Mailable $email) use ($mailable) {
                if ($email == $mailable) {
                    return true;
                }
                return false;
            })->andReturn(false)->ordered();
        }
        
        $generator = $this->getMockGenerator($mocks);
        
        $mailerService = new MailerService($generator, $this->sender, $this->replyTo, $this->defaultProviderName);
        $mailerService->sendEmail($this->email);
    }
    
    private function getMockGenerator(array $mocks)
    {
        return new RewindableGenerator(function () use ($mocks) {
            foreach ($mocks as $mock) {
                yield $mock;
            }
        }, count($mocks));
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
