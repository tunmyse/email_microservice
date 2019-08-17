<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $fillable = ['address'];
    
    /**
     * Email of the re.
     */
    public function email()
    {
        return $this->belongsTo('App\Email');
    }
}
