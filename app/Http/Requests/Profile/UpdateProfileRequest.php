<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use PasswordRule\PasswordRule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => ['nullable', new PasswordRule()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.confirmed' => \trans('auth.registration.validation.confirmed_pass'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $ret = [
            'name' => $this->input('name'),
        ];
        $password = $this->input('password');
        if (!empty($password)) {
            $ret['password'] = Hash::make($password);
        }

        return $ret;
    }
}
