<?php

namespace App\Services;

use App\Contracts\MailerProvider;
use App\Email;
use App\Mail\Message;
use Illuminate\Container\RewindableGenerator;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class MailerService
{

    /**
     * Email sender
     *
     * @var string
     */
    private $from;

    /**
     * Email to reply to
     *
     * @var string
     */
    private $replyTo;

    /**
     *  Name of the primary MailerProvider
     *
     * @var string
     */
    private $defaultProviderName;

    /**
     *  Array of MailerProviders
     *
     * @var array
     */
    private $providers = [];

    /**
     *  Mapping of provider name and index
     *
     * @var array
     */
    private $providerNameMap = [];
        
    public function __construct(RewindableGenerator $mailerProviders, string $from, string $replyTo, string $defaultProviderName)
    {
        $this->from = $from;
        $this->replyTo = $replyTo;
        $this->defaultProviderName = $defaultProviderName;
        $numProviders = $mailerProviders->count();
        
        if ($numProviders < 2) {
            throw new InvalidArgumentException(sprintf('MailerService requires at least 2 mailer provider services, %s provided!', $numProviders));
        }

        foreach ($mailerProviders as $provider) {
            if (!is_object($provider) || !$provider instanceof MailerProvider) {
                throw new InvalidArgumentException('All mailer provider services must implement "MailerProvider" interface!');
            }

            if ($this->isDefaultProvider($provider)) {
                array_unshift($this->providerNameMap, $provider->getProviderName());
                array_unshift($this->providers, $provider);
            } else {
                array_push($this->providerNameMap, $provider->getProviderName());
                array_push($this->providers, $provider);
            }
        }
    }

    public function sendEmail(Email $email)
    {
        $mailable = $this->buildMailableFromModel($email);

        /** @var MailerProvider $provider */
        foreach ($this->providers as $provider) {
            $isSuccessful = $provider->sendEmail($mailable);

            if ($isSuccessful) {
                return;
            }
        }
    }

    private function buildMailableFromModel(Email $email)
    {
        $recipients = $email->recipients()->pluck('address')->all();
        $format = $email->format;
        $body = $email->body;
        
        if ($format == 'markdown') {
            $body = Markdown::parse($body)->toHtml();
            $format = 'html';
        }
        
        return new Message($recipients, $this->from, $this->replyTo, $email->subject, $body, $format, $email->id);
    }

    public function getMailerProvider(string $providerName)
    {
        $index = array_search($providerName, $this->providerNameMap);
        
        if ($index === false) {
            throw new RuntimeException("No provider exists for the specified provider name!");
        }
        
        return $this->providers[$index];
    }
    
    private function isDefaultProvider(MailerProvider $provider)
    {
        return $this->defaultProviderName == $provider->getProviderName();
    }
}
