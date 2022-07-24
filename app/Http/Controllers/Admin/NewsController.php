<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Admin;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\News\CreateNewsRequest;
use CzechitasApp\Http\Requests\News\UpdateNewsRequest;
use CzechitasApp\Models\News;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\NewsService;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NewsController extends Controller
{
    private NewsService $newsService;

    private BreadcrumbService $breadcrumbService;

    public function __construct(NewsService $newsService, BreadcrumbService $breadcrumbService)
    {
        $this->newsService = $newsService;
        $this->breadcrumbService = $breadcrumbService;
        $breadcrumbService->addLevel('admin.news.index', \trans('news.title'));
    }

     /**
      * Display a listing of the resource.
      */
    public function index(): View
    {
        $this->authorize('list', News::class);
        $news = $this->newsService->getNewsListQuery()->get();

        return \view('admin.news.list', \compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', News::class);
        $this->breadcrumbService->addLevelWithUrl('', \trans('app.actions.create'));

        return \view('admin.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateNewsRequest $request): RedirectResponse
    {
        $this->authorize('create', News::class);
        $news = $this->newsService->insert($request->getData());

        Alert::success(\trans('news.success.flash_create', ['title' => $news->title]))->flash();

        return \redirect()->route('admin.news.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news): View
    {
        $this->authorize('update', $news);
        $this->breadcrumbService->addLevelWithUrl('', \trans('app.actions.edit'));

        return \view('admin.news.edit', \compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $this->authorize('update', $news);
        $this->newsService->setContext($news)->update($request->getData());

        Alert::success(\trans('news.success.flash_update', ['title' => $news->title]))->flash();

        return \redirect()->route('admin.news.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news): RedirectResponse
    {
        $this->authorize('delete', $news);
        $this->newsService->setContext($news)->delete();

        Alert::success(\trans('news.success.flash_delete', ['title' => $news->title]))->flash();

        return \redirect()->route('admin.news.index');
    }
}
