<?php namespace Gzero\Core\Validators\Rules;

use Gzero\Core\Services\LanguageService;
use Illuminate\Contracts\Validation\Rule;

class LanguageCodeIsActive implements Rule {

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
        /** @var LanguageService $languageService */
        $languageService = resolve(LanguageService::class);

        return true;
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