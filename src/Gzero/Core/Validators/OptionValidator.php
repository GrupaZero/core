<?php namespace Gzero\Core\Validators;

class OptionValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'update' => [
            'key'   => ['required', 'exists:options,key'],
            'value' => 'required'
        ]
    ];

}
