<?php namespace Gzero\Core\Validators;

class BaseUserValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'login'    => [
            'email'    => 'required|email',
            'password' => 'required'
        ],
        'register' => [
            'email'      => 'required|email|unique:users',
            'name'       => 'required|min:3|unique:users',
            'password'   => 'required|min:6',
            'first_name' => 'nullable|min:2|regex:/^([^0-9]*)$/', // without numbers
            'last_name'  => 'nullable|min:2|regex:/^([^0-9]*)$/' // without numbers
        ],
        'remind'   => [
            'email' => 'required|email',
        ],
        'reset'    => [
            'email'                 => 'required|email',
            'password'              => 'required|min:6|same:password_confirmation',
            'password_confirmation' => 'required|min:6|same:password',
            'token'                 => '',
        ]
    ];

    /**
     * @var array
     */
    protected $filters = [];
}
