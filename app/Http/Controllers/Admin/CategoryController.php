<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Admin;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\Category\CreateCategoryRequest;
use CzechitasApp\Http\Requests\Category\UpdateCategoryRequest;
use CzechitasApp\Models\Category;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\CategoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CategoryController extends Controller
{
    private BreadcrumbService $breadcrumbService;

    private CategoryService $categoryService;

    public function __construct(BreadcrumbService $breadcrumbService, CategoryService $categoryService)
    {
        $this->breadcrumbService = $breadcrumbService;
        $this->categoryService = $categoryService;
        $breadcrumbService->addLevel('admin.categories.index', \trans('categories.title'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('list', Category::class);
        $categories = $this->categoryService->getListQuery()->get();

        return \view('admin.categories.list', \compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Category::class);
        $parents = $this->categoryService->getCategoriesQuery()->get();
        $this->breadcrumbService->addLevelWithUrl('', \trans('app.actions.create'));

        return \view('admin.categories.create', \compact('parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $category = $this->categoryService->insert($request->getData());

        Alert::success(\trans('categories.success.flash_create', ['name' => $category->name]))->flash();

        return \redirect()->route('admin.categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(): RedirectResponse
    {
        return \redirect()->route('admin.categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        $this->authorize('update', $category);
        $this->addBreadcrumb($category)->addLevelWithUrl('', \trans('app.actions.edit'));

        $image = $this->categoryService->setContext($category)->getImageUrl();

        return \view('admin.categories.edit', \compact('category', 'image'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $this->categoryService->setContext($category)->update($request->getData());

        Alert::success(\trans('categories.success.flash_update', ['name' => $category->name]))->flash();

        return \redirect()->route('admin.categories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function reorder(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $direction = null;
        if ($request->input('up') !== null) {
            $direction = -1;
        }
        if ($request->input('down') !== null) {
            $direction = 1;
        }
        $success = false;
        if (!empty($direction)) {
            $success = $this->categoryService->setContext($category)->move($direction);
        }
        if ($success) {
            Alert::success(\trans('categories.success.flash_reorder', ['name' => $category->name]))->flash();
        } else {
            Alert::error(\trans('categories.error.flash_reorder', ['name' => $category->name]))->flash();
        }

        return \redirect()->route('admin.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        $this->categoryService->setContext($category)->delete();

        Alert::success(\trans('categories.success.flash_delete', ['name' => $category->name]))->flash();

        return \redirect()->route('admin.categories.index');
    }

    protected function addBreadcrumb(Category $category): BreadcrumbService
    {
        return $this->breadcrumbService->addLevel('admin.categories.show', $category->name, [$category]);
    }
}
