<?php

namespace App\Contracts;

use Iterator;

interface MailerProvider
{
    
    /**
     * Send email using this provider.
     *
     * @param  Mailable  $email
     * @return bool
     */
    public function sendEmail(Mailable $email): bool;
        
    /**
     * Return the name of this provider.
     *
     * @return string
     */
    public function getProviderName();
    
    /**
     * Returns an iterator over processed mail events.
     *
     * @param array $events
     * @return Iterator
     */
    public function processMailEvents(array $events);
}
