<?php namespace Gzero\Core\Validators;

class FileValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'create' => [
            'type'          => 'required|in:image,document,video,music',
            'title'         => 'required',
            'language_code' => 'required|in:pl,en,de,fr',
            'file'          => 'required|file',
            'info'          => 'array',
            'is_active'     => 'boolean',
            'description'   => '',
        ],
        'update' => [
            'info'      => 'array',
            'is_active' => 'boolean',
        ]
    ];

    /** @var array */
    protected $filters = [
        'title' => 'trim'
    ];
}
