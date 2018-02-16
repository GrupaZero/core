<?php namespace Gzero\Core\Validators;

use Gzero\Core\Validators\Rules\LanguageCodeIsActive;
use Gzero\Core\Validators\Rules\TimezoneIsValid;

class UserValidator extends AbstractValidator {

    /**
     * Returns validation rules for login
     *
     * @return array
     */
    public function login()
    {
        return [
            'email'    => 'required|email',
            'password' => 'required'
        ];
    }

    /**
     * Returns validation rules for register
     *
     * @return array
     */
    public function register()
    {
        return [
            'email'         => 'required|email|unique:users',
            'name'          => 'required|min:3|unique:users',
            'password'      => 'required|min:6',
            'first_name'    => 'nullable|min:2|regex:/^([^0-9]*)$/', // without numbers
            'last_name'     => 'nullable|min:2|regex:/^([^0-9]*)$/', // without numbers
            'language_code' => ['nullable', 'sometimes', new LanguageCodeIsActive],
            'timezone'      => ['nullable', 'sometimes', new TimezoneIsValid]
        ];
    }

    /**
     * Returns validation rules for login
     *
     * @return array
     */
    public function remind()
    {
        return [
            'email' => 'required|email',
        ];
    }

    /**
     * Returns validation rules for reset
     *
     * @return array
     */
    public function reset()
    {
        return [
            'email'                 => 'required|email',
            'password'              => 'required|min:6|same:password_confirmation',
            'password_confirmation' => 'required|min:6|same:password',
            'token'                 => '',
        ];
    }

    /**
     * Returns validation rules for update
     *
     * @return array
     */
    public function update()
    {
        return [
            'email'      => 'required|email|unique:users,email,@user_id',
            'name'       => 'required|min:3|unique:users,name,@user_id',
            'first_name' => '',
            'last_name'  => ''
        ];
    }

    /**
     * Returns validation rules for updateMe
     *
     * @return array
     */
    public function updateMe()
    {
        return [
            'email'                 => 'required|email|unique:users,email,@user_id',
            'name'                  => 'required|min:3|unique:users,name,@user_id',
            'first_name'            => 'min:2|regex:/^([^0-9]*)$/', // without numbers
            'last_name'             => 'min:2|regex:/^([^0-9]*)$/', // without numbers
            'password'              => 'sometimes|min:6|same:password_confirmation|required_with:password_confirmation',
            'password_confirmation' => 'sometimes|min:6|same:password|required_with:password',
            'language_code'         => ['nullable', 'sometimes', new LanguageCodeIsActive],
            'timezone'              => ['nullable', 'sometimes', new TimezoneIsValid]
        ];
    }
}
