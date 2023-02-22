<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\User;

use CzechitasApp\Models\Enums\UserRole;
use CzechitasApp\Rules\EmailRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Testing to unique rule
     */
    protected function getUniqueRule(string $table = 'users', string $column = 'NULL'): Unique
    {
        return Rule::unique($table, $column);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name'                  => 'required|string|max:255',
            'email'                 => ['required', new EmailRule(), 'max:190', $this->getUniqueRule()],
            'role'                  => ['required', Rule::in(UserRole::getAvailableValues())],
            'password'              => ['required', Password::defaults(), 'confirmed'],
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => \trans('users.validation.email_unique'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $data = [
            'name'          => $this->input('name'),
            'email'         => $this->input('email'),
            'role'          => $this->input('role'),
            'password'      => Hash::make($this->input('password')),
        ];

        return $data;
    }
}
