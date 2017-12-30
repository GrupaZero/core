<?php namespace Gzero\Core\Validators;

class FileValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'create' => [
            'type'          => 'required|in:image,document,video,music',
            'file'          => 'required',
            'info'          => '',
            'is_active'     => 'boolean',
            'language_code' => 'required_with:title|in:pl,en,de,fr',
            'title'         => 'required_with:language_code',
            'description'   => '',
        ],
        'update' => [
            'info'      => '',
            'is_active' => 'boolean',
        ]
    ];

    /** @var array */
    protected $filters = [
        'title' => 'trim'
    ];
}
