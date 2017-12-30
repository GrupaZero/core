<?php namespace Gzero\Core\Validators;

class FileTranslationValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'create' => [
            'language_code' => 'required|in:pl,en,de,fr',
            'title'         => 'required',
            'description'   => ''
        ]
    ];

    /** @var array */
    protected $filters = [
        'title'       => 'trim',
        'description' => 'trim'
    ];
}
