<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = ['subject', 'body', 'format'];
    
    /**
     * Recipients of the email.
     */
    public function recipients()
    {
        return $this->hasMany('App\Recipient');
    }
}
