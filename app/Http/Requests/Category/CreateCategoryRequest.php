<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Requests\Category;

use CzechitasApp\Services\Models\CategoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\In;

class CreateCategoryRequest extends FormRequest
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
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
    public function rules(): array
    {
        $rules = [
            'name'              => 'required|string|max:50',
            'cover_img'         => 'required|mimes:jpeg,png',
            'content'           => 'nullable|string|max:65000',
        ];

        if ($this->input('parent_id') !== null) {
            $rules['parent_id'] = [
                'required',
                new In($this->categoryService->getCategoriesQuery()->pluck('id')->toArray()),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return [
            'parent_id'         => $this->input('parent_id') !== null ? (int)$this->input('parent_id') : null,
            'name'              => $this->input('name'),
            'cover_img'         => $this->file('cover_img'),
            'content'           => \secureMarkdown($this->input('content')),
        ];
    }
}
