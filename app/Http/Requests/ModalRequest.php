<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class ModalRequest extends FormRequest
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
    abstract public function rules(): array;

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            // Add custom unique error to show popup again
            if ($validator->errors()->isNotEmpty()) {
                $validator->errors()->add('anyErrorPushId', $this->input('anyErrorPushId') ?? '');
            }
        });
    }

    /**
     * @param  array<string, mixed> $toMerge
     * @return array<string, mixed>
     */
    public function getData(array $toMerge = []): array
    {
        return \array_merge([
            'send_notification' => $this->input('send_notification') === '1',
        ], $toMerge);
    }
}
