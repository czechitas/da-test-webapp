<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers;

use CzechitasApp\Services\BreadcrumbService;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    private BreadcrumbService $breadcrumbService;

    public function __construct(BreadcrumbService $breadcrumbService)
    {
        $this->breadcrumbService = $breadcrumbService;
    }

    public function teachers(): View
    {
        $this->breadcrumbService->addLevel(Route::currentRouteName(), \trans('pages.breadcrumbs.teachers'));

        return \view('static.teachers');
    }

    public function parents(): View
    {
        $this->breadcrumbService->addLevel(Route::currentRouteName(), \trans('pages.breadcrumbs.parents'));

        return \view('static.parents');
    }

    public function contact(): View
    {
        $this->breadcrumbService->addLevel(Route::currentRouteName(), \trans('pages.breadcrumbs.contact'));

        return \view('static.contact');
    }

    public function markdown(): View
    {
        return \view('static.markdown');
    }
}
