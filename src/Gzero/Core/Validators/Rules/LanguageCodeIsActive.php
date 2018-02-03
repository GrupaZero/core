<?php namespace Gzero\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class LanguageCodeIsActive implements Rule {
    /**
     * LanguageCodeIsActive constructor.
     */
    public function __construct()
    {
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  string $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "this language is not active";
    }
}