<?php

namespace App\Contracts;

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
}
