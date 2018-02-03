<?php namespace Gzero\Core\Validators\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use function timezone_open;

class TimezoneIsValid implements Rule {

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            timezone_open($value);
        } catch (Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('gzero-core::user.invalid_timezone');
    }
}