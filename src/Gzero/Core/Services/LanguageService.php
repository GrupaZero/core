<?php namespace Gzero\Core\Services;

use Gzero\Core\Models\Language;
use Gzero\Core\Repositories\RepositoryException;
use Illuminate\Support\Collection;

class LanguageService {

    /**
     * All languages
     *
     * @var Collection
     */
    protected $languages;

    /**
     * LangRepository constructor
     *
     * @param Collection $languages Collection of languages
     */
    public function __construct(Collection $languages)
    {
        $this->languages = $languages;
    }

    /**
     * Refresh $languages cache
     *
     * @return void
     */
    public function refresh()
    {
        $this->languages = Language::all();
        cache()->forever('languages', $this->languages);
    }

    /**
     * Get lang by lang code
     *
     * @param string $code Lang code eg. "en"
     *
     * @throws RepositoryException
     * @return \Gzero\Core\Models\Language
     */
    public function getByCode($code)
    {
        return $this->languages->filter(
            function ($language) use ($code) {
                return $language->code == $code;
            }
        )->first();
    }

    /**
     * Get current language
     *
     * @return \Gzero\Core\Models\Language
     */
    public function getCurrent()
    {
        return $this->getByCode(app()->getLocale());
    }

    /**
     * Get all languages
     *
     * @return Collection
     */
    public function getAll()
    {
        return $this->languages;
    }

    /**
     * Get all enabled languages
     *
     * @return Collection
     */
    public function getAllEnabled()
    {
        return $this->languages->filter(
            function ($lang) {
                return ($lang->is_enabled) ? $lang : false;
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function getDefault()
    {
        return $this->languages->first(function ($value) {
            return $value->is_default;
        });
    }
}
