<?php

namespace App\Mail;

use App\Contracts\Mailable;

class Message implements Mailable
{
    
    /**
     * Email addresses to send emails to
     *
     * @var array
     */
    private $to;
    
    /**
     * Sender address
     *
     * @var string
     */
    private $from;
    
    /**
     * Reply address
     *
     * @var string
     */
    private $replyTo;
    
    /**
     * Email subject
     *
     * @var string
     */
    private $subject;
    
    /**
     * Email body
     *
     * @var string
     */
    private $body;
        
    /**
     * Email format
     *
     * @var string
     */
    private $format;
    
    /**
     * Unique Id of the message
     *
     * @var type
     */
    private $messageId;
    
    public function __construct($to, $from, $replyTo, $subject, $body, $format, $messageId)
    {
        $this->to = $to;
        $this->from = $from;
        $this->replyTo = $replyTo;
        $this->subject = $subject;
        $this->body = $body;
        $this->format = $format;
        $this->messageId = $messageId;
    }

    public function getTo(): array
    {
        return $this->to;
    }
    
    public function getFrom(): string
    {
        return $this->from;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }
    
    public function getSubject(): string
    {
        return $this->subject;
    }
    
    public function getBody(): string
    {
        return $this->body;
    }
    
    public function getFormat(): string
    {
        return $this->format;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }
}
