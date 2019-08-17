<?php

namespace App\Services;

use App\Contracts\Mailable;
use App\Contracts\MailerProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

class MailjetProvider implements MailerProvider
{

    /**
     * API endpoint
     *
     * @var string
     */
    private $endpoint = 'https://api.mailjet.com/v3.1/send';
    
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


    public function __construct(ClientInterface $client, string $apiUsername, string $apiPassword)
    {
        $this->client = $client;
        
        if (empty($apiUsername) || empty($apiPassword)) {
            throw new InvalidArgumentException('Please provide valid "Username" and "Password" values to access the Mailjet services!');
        }
        
        $this->options['auth'] = [$apiUsername, $apiPassword];
    }

    public function sendEmail(Mailable $email): bool
    {
        $this->options['json'] = $this->buildRequestParam($email);
        
        try {
            $response = $this->client->request('POST', $this->endpoint, $this->options);
            if ($response->getStatusCode() === 200) {
                return true;
            }
        } catch (GuzzleException $ex) {
        }
        
        return false;
    }

    private function buildRequestParam(Mailable $email)
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
    
    public function getProviderName(): string
    {
        return 'mailjet';
    }
}
