<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\User;

use CzechitasApp\Models\User;
use Illuminate\Validation\Rules\Unique;
use PasswordRule\PasswordRule;

class UpdateUserRequest extends CreateUserRequest
{
    /**
     * Testing to unique rule
     */
    protected function getUniqueRule(string $table = 'users', string $column = 'NULL'): Unique
    {
        /** @var User $user */
        $user = $this->route('user');

        return parent::getUniqueRule($table, $column)->ignore($user->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['password'] = ['nullable', new PasswordRule(), 'confirmed'];

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $data = parent::getData();

        if (empty($this->input('password'))) {
            unset($data['password']);
        }

        return $data;
    }
}
