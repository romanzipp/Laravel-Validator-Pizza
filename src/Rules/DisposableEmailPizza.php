<?php

namespace romanzipp\ValidatorPizza\Rules;

use Illuminate\Contracts\Validation\Rule;
use romanzipp\ValidatorPizza\Checker;

class DisposableEmailPizza implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $checker = new Checker;

        return $check->allowedEmail($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The given :attribute is not allowed.';
    }
}
