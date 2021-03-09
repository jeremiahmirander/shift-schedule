<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'    => [
                'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->getAuthIdentifier()),
            ],
            'password' => ['string', 'min:8'],
        ];
    }
}
