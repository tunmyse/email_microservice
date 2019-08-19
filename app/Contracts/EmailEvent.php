<?php

namespace App\Contracts;

interface EmailEvent
{
    
    /**
     * Get the email.
     *
     * @return string
     */
    public function getEmail();
    
    /**
     * Get the status of the email sent to the recipient.
     *
     * @return string
     */
    public function getStatus();
            
    /**
     * Get the message identifier for the email.
     *
     * @return mixed
     */
    public function getMessageId();
    
    /**
     * Get the name of the mailer provider.
     *
     * @return string
     */
    public function getProviderName();
}
