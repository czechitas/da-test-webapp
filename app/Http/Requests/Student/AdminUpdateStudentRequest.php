<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\Student;

use CzechitasApp\Http\Requests\Student\AdminCreateStudentRequest;
use CzechitasApp\Models\Term;

class AdminUpdateStudentRequest extends AdminCreateStudentRequest
{
    protected bool $keepTerm = false;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(?Term $term = null): array
    {
        $term = $this->route()->student->term;
        if ($term->isPossibleAdminChangeTerm()) {
            if (empty($this->input('term_id'))) {
                return ['term_id' => 'required'];
            }
            $term = $this->termService->findTermOrFail((int)$this->input('term_id'));
        } else {
            $this->keepTerm = true;
        }
        $rules = parent::rules($term);

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(bool $addTerm = false): array
    {
        $data = parent::getData($addTerm);

        if ($this->keepTerm) {
            unset($data['term_id']);
        }

        return $data;
    }
}
