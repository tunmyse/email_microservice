<?php

namespace App\Contracts;

interface Mailable
{
    
    /**
     * Get the recipients of the email.
     *
     * @return array
     */
    public function getTo();
    
    /**
     * Get the email sender.
     *
     * @return string
     */
    public function getFrom();
    
    /**
     * Get the reply email.
     *
     * @return string
     */
    public function getReplyTo();
    
    /**
     * Get the subject of the email.
     *
     * @return string
     */
    public function getSubject();
    
    /**
     * Get the body of the email.
     *
     * @return string
     */
    public function getBody();
    
    /**
     * Get the body of the email.
     *
     * @return string
     */
    public function getFormat();
    
    /**
     * Get a unique identifier for the email.
     *
     * @return mixed
     */
    public function getMessageId();
}
