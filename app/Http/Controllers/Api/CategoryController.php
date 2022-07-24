<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Api;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Models\Category;
use CzechitasApp\Services\Models\CategoryService;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(): Response
    {
        /** @var Collection<Category> $categories */
        $categories = $this->categoryService->getAllList()->get();

        foreach ($categories as $category) {
            $category->imagePath = $this->categoryService->setContext($category)->getImageUrl();

            foreach ($category->children as $subCategory) {
                $subCategory->imagePath = $this->categoryService->setContext($subCategory)->getImageUrl();
            }
        }

        return \response()->json($categories);
    }
}
