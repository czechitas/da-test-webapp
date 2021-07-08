<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\Student;

use CzechitasApp\Models\Enums\StudentPaymentType;
use CzechitasApp\Models\Term;
use CzechitasApp\Rules\EmailRule;
use CzechitasApp\Services\Models\TermService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateStudentRequest extends FormRequest
{
    /** @var TermService */
    protected $termService;

    /** @var Term|null */
    protected $term = null;

    public function __construct(TermService $termService)
    {
        $this->termService = $termService;
    }

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
    public function rules(?Term $term = null): array
    {
        if (empty($term) || !$term->exists) {
            if (empty($this->input('term_id'))) {
                return ['term_id' => 'required'];
            }
            $term = $this->termService->findTermOrFail((int)$this->input('term_id'));
        }
        $this->term = $term;
        $minBirthday = $term->start->subYears(\config('czechitas.student.minimum_age_term_starts'))->format('Y-m-d');

        $rules = [
            'parent_name'       => 'required|string|max:255',
            'forename'          => 'required|string|max:255',
            'surname'           => 'required|string|max:255',
            'birthday'          => 'required|date|before_or_equal:' . $minBirthday,
            'email'             => ['required', new EmailRule(), 'max:255'],
            'restrictions'      => 'required_if:restrictions_yes,1|string|max:255',
            'note'              => 'nullable|string|max:150',
            // 'terms_conditions'  => 'accepted',
        ];

        if ($term->select_payment) {
            $rules['payment']   = 'required|in:' . \implode(',', StudentPaymentType::getAvailableValues());
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        if ($this->input('term_id') === null) {
            return;
        }
        $validator->after(function (Validator $validator): void {
            if (!$this->term->isPossibleLogin()) {
                $validator->errors()->add('term_id', \trans('validation.in'));
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'restrictions.required_if' => \trans('validation.required'),
            'birthday.before_or_equal' => \trans(
                'students.form.validation.birthday_min_age',
                ['years' => \config('czechitas.student.minimum_age_term_starts')]
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(bool $addTerm = false): array
    {
        $data = [
            'term_id'           => $this->input('term_id'),
            'parent_name'       => \formatNameCase($this->input('parent_name')),
            'forename'          => \formatNameCase($this->input('forename')),
            'surname'           => \formatNameCase($this->input('surname')),
            'birthday'          => \getCarbon($this->input('birthday')),
            'email'             => $this->input('email'),
            'payment'           => $this->input('payment'),
            'restrictions'      => $this->input('restrictions_yes') ? $this->input('restrictions') : null,
            'note'              => $this->input('note'),
        ];

        if ($this->term->select_payment) {
            $data['payment']   = $this->input('payment');
        }
        if ($addTerm) {
            $data['term'] = $this->term;
        }

        return $data;
    }
}
