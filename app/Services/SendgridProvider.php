<?php

namespace App\Services;

use App\Contracts\Mailable;
use App\Contracts\MailerProvider;
use App\Mail\DefaultEmailEvent;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Iterator;

class SendgridProvider implements MailerProvider
{

    /**
     * API endpoint
     *
     * @var string
     */
    private $endpoint = 'https://api.sendgrid.com/v3/mail/send';
    
    /**
     * HTTP client
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * Request options
     *
     * @var array
     */
    private $options = [];


    public function __construct(ClientInterface $client, string $apiToken)
    {
        $this->client = $client;
        
        if (empty($apiToken)) {
            throw new InvalidArgumentException('Please provide a valid "API Token" value to access the Sendgrid services!');
        }
        
        $this->options['headers'] = ['Authorization' => 'Bearer ' . $apiToken];
    }

    public function sendEmail(Mailable $email): bool
    {
        $this->options['json'] = $this->buildRequestParam($email);
        
        try {
            $response = $this->client->request('POST', $this->endpoint, $this->options);
            if ($response->getStatusCode() === 202) {
                return true;
            }
        } catch (GuzzleException $ex) {
        }
        
        return false;
    }

    private function buildRequestParam(Mailable $email)
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
            'content' => [[
            'type' => 'text/' . $email->getFormat(),
            'value' => $email->getBody()
                ]],
            'custom_args' => ['app_message_id' => $email->getMessageId()]
        ];

        return $reqParams;
    }
    
    public function processMailEvents(array $events): Iterator
    {
        $providerName = $this->getProviderName();
        
        return (function () use ($events, $providerName) {
            foreach ($events as $event) {
                if (empty($event['app_message_id'])) {
                    continue;
                }
                
                yield new DefaultEmailEvent($event['email'], $event['event'], $event['app_message_id'], $providerName);
            }
        })();
    }
    
    public function getProviderName(): string
    {
        return 'sendgrid';
    }
}
