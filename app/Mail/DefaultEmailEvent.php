<?php

namespace App\Mail;

use App\Contracts\EmailEvent;

class DefaultEmailEvent implements EmailEvent
{
    /**
     *
     * @var $email
     */
    private $email;
    
    private $event;
    
    private $messageId;
    
    private $providerName;
    
    public function __construct(string $email, string $event, string $messageId, string $providerName)
    {
        $this->email = $email;
        $this->event = $event;
        $this->messageId = $messageId;
        $this->providerName = $providerName;
    }

    /**
     * Get the email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Get the status of the email sent to the recipient.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->event;
    }
            
    /**
     * Get the message identifier for the email.
     *
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }
    
    /**
     * Get the name of the mailer provider.
     *
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }
}
