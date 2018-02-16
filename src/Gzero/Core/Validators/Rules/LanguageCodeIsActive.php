<?php namespace Gzero\Core\Validators\Rules;

use Gzero\Core\Services\LanguageService;
use Illuminate\Contracts\Validation\Rule;

class LanguageCodeIsActive implements Rule {

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute attribute
     * @param  string $value     value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        /** @var LanguageService $languageService */
        $languageService = resolve(LanguageService::class);

        $language = $languageService->getByCode($value);
        return $language ? $language->is_enabled : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('gzero-core::user.invalid_language');
    }
}
