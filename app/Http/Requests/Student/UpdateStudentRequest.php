<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\Student;

use CzechitasApp\Http\Requests\Student\CreateStudentRequest;
use CzechitasApp\Models\Term;

class UpdateStudentRequest extends CreateStudentRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(?Term $term = null): array
    {
        $term = $this->route()->student->term;
        $rules = parent::rules($term);

        // unset($rules['forename']);
        // unset($rules['surname']);
        unset($rules['terms_conditions']);

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(bool $addTerm = false): array
    {
        $data = parent::getData($addTerm);

        if ($data['term_id'] === null) {
            unset($data['term_id']);
        }

        // unset($data['forename']);
        // unset($data['surname']);

        return $data;
    }
}
