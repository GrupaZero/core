<?php namespace Gzero\Core\ViewModels;

class FileViewModel {

    /** @var array */
    protected $data;

    /** @var array */
    protected $author;

    /** @var string */
    protected $type;

    /** @var array */
    protected $translation;

    /** @var array */
    protected $translations;

    /** @var array */
    protected $allowedAttributes = [
        'id',
        'name',
        'extension',
        'size',
        'mime_type',
        'info',
        'is_active'
    ];

    /**
     * ContentPresenter constructor.
     *
     * @param array $data data to create presenter instance
     */
    public function __construct(array $data)
    {
        $this->data         = array_only($data, $this->allowedAttributes);
        $this->translations = array_get($data, 'translations', []);
        $this->author       = new UserViewModel(array_get($data, 'author', []));
        $this->type         = array_get($data, 'type');

        $this->translation = array_first($this->translations, function ($translation) {
            return $translation['language_code'] === app()->getLocale();
        }, [
            'title'       => null,
            'description' => null
        ]);
    }

    /** @return mixed */
    public function isActive()
    {
        return array_get($this->data, 'is_active', false);
    }

    /** @return integer */
    public function id()
    {
        return array_get($this->data, 'id');
    }

    /** @return string */
    public function name()
    {
        return array_get($this->data, 'name');
    }

    /** @return string */
    public function extension()
    {
        return array_get($this->data, 'extension');
    }

    /** @return string */
    public function size()
    {
        return array_get($this->data, 'size');
    }

    /** @return string */
    public function mimeType()
    {
        return array_get($this->data, 'mime_type');
    }

    /** @return string */
    public function info()
    {
        return array_get($this->data, 'info');
    }

    /** @return string */
    public function uploadPath()
    {
        if ($this->type === null) {
            return null;
        }

        return str_plural($this->type['name']) . '/' . $this->name() . '.' . $this->extension();
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function title(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'title');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'title');
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function description(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'description');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'description');
    }

    /**
     * This function returns author first and last name
     *
     * @return FileViewModel
     */
    public function author()
    {
        return optional($this->author);
    }
}
