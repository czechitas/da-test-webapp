<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Admin;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Models\Category;
use CzechitasApp\Models\Term;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\ExcelExportService;
use CzechitasApp\Services\Models\TermService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ExportController extends Controller
{
    private ExcelExportService $exportService;

    private TermService $termService;

    public function __construct(
        ExcelExportService $exportService,
        BreadcrumbService $breadcrumbService,
        TermService $termService,
    ) {
        $this->exportService = $exportService;
        $breadcrumbService->addLevel('admin.exports.index', \trans('exports.title'));
        $this->termService = $termService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('exports.list');

        /** @var Collection<Term> $terms */
        $terms = $this->termService
            ->getTermsForExport()
            ->orderBy('start')
            ->get();

        $categories = [];

        /** @var Collection<Category> $category */
        foreach ($terms->sortBy('category.name')->groupBy('category_id') as $category) {
            $categories[] = $category->sortBy('start');
        }

        return \view('admin.exports.list', \compact('categories'));
    }

    private function validateDateRequest(Request $request, string $keyType): void
    {
        Validator::make($request->all(), [
            $keyType . '.start'   => 'required|date_format:d.m.Y',
            $keyType . '.end'     => "required|date_format:d.m.Y|after_or_equal:{$keyType}.start",
        ])->validate();
    }

    private function validateTermRequest(Request $request, string $keyType): void
    {
        Validator::make($request->all(), [
            $keyType . '.term_id'   => 'required|exists:terms,id',
        ])->validate();
    }

    public function fullTerm(Request $request): void
    {
        $this->authorize('exports.fullTerm');

        $this->validateTermRequest($request, 'full_term');
        $this->exportService->fullTermExport(
            $this->termService->findTermOrFail(
                (int)$request->input('full_term.term_id'),
            ),
        )->sendToBrowser('Termín.xlsx');
    }

    public function overUnderPaid(Request $request): void
    {
        $this->authorize('exports.overUnderPaid');

        $this->validateDateRequest($request, 'over_under_paid');
        $this->exportService->exportOverUnderPaid(
            \getCarbon($request->input('over_under_paid.start')),
            \getCarbon($request->input('over_under_paid.end')),
        )->sendToBrowser('Přeplatky a nedoplatky.xlsx');
    }
}
