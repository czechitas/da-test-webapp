<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\Api\Profile;

use CzechitasApp\Http\Requests\Api\BaseFormRequest;
use CzechitasApp\Rules\EmailRule;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', new EmailRule(), 'unique:users'],
            'password' => ['required', Password::defaults()],
        ];
    }
}
