<?php namespace Gzero\Core\Validators;

class RouteValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'create' => [
            'language_code' => 'required|in:pl,en,de,fr',
            'is_active'     => 'bool',
            'path'          => 'required'
        ]
    ];

}
