<?php

namespace App\Rules;

use Illuminate\Validation\Validator;

class Recipients
{
        
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  Validator  $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (is_array($value) && $this->checkEmails($attribute, $value, $validator)) {
            return true;
        }
        
        return false;
    }
    
    private function checkEmails($attribute, array $emails, Validator $validator)
    {
        foreach ($emails as $email) {
            if (!$validator->validateEmail($attribute, $email)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message($message, $attribute, $rule, $parameters)
    {
        return sprintf('The %s must be an array of valid email addresses.', $attribute);
    }
}
