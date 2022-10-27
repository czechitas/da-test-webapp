<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers;

use CzechitasApp\Models\Category;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\CategoryService;
use CzechitasApp\Services\Models\NewsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    private CategoryService $categoryService;

    private BreadcrumbService $breadcrumbService;

    private NewsService $newsService;

    public function __construct(
        CategoryService $categoryService,
        BreadcrumbService $breadcrumbService,
        NewsService $newsService,
    ) {
        $this->categoryService = $categoryService;
        $this->breadcrumbService = $breadcrumbService;
        $this->newsService = $newsService;
        $breadcrumbService->fresh(null, \trans('app.homepage.breadcrumb'));
    }

    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        $categories = $this->categoryService->getHomepageListQuery()->get()->reverse();
        $news = $this->newsService->getNewsListQuery()->limit(3)->get();

        return \view('home.intro', \compact('categories', 'news'));
    }

    public function category(Category $category): View
    {
        $this->breadcrumbService->addLevel('home.category', $category->name, $category);
        $categories = $this->categoryService->getHomepageListQuery($category->id)->get();

        return \view('home.category', \compact('category', 'categories'));
    }

    public function error404(): void
    {
        \abort(404);
    }

    public function apiError404(): void
    {
        \abort(404, 'undefined API path');
    }

    public function apiary(): RedirectResponse
    {
        $url = \config('czechitas.apiary');

        if (empty($url)) {
            \abort(404, 'undefined API path');
        }

        return \redirect($url);
    }
}
