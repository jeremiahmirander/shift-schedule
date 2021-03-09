<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->attributes->get('sanctum')) {
            return [
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
            ];
        } else {
            return [
                'device_name' => ['required', 'string', 'min:3', 'max:64'],
                'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password'    => ['required', 'string', 'min:8'],
            ];
        }
    }
}
