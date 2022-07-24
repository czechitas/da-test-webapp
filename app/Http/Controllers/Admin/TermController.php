<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Admin;

use CzechitasApp\Http\Contracts\RedirectBack as RedirectBackContract;
use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\Term\CreateTermRequest;
use CzechitasApp\Http\Requests\Term\UpdateTermRequest;
use CzechitasApp\Http\Requests\UpdateFlagRequest;
use CzechitasApp\Http\Traits\RedirectBack;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\Term;
use CzechitasApp\Modules\AjaxDataTables\Column;
use CzechitasApp\Modules\AjaxDataTables\DataTable;
use CzechitasApp\Modules\AjaxDataTables\RelationCountColumn;
use CzechitasApp\Modules\AjaxDataTables\TableColumns;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\CategoryService;
use CzechitasApp\Services\Models\StudentService;
use CzechitasApp\Services\Models\TermService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TermController extends Controller implements RedirectBackContract
{
    use RedirectBack;

    private TermService $termService;

    private BreadcrumbService $breadcrumbService;

    private CategoryService $categoryService;

    private StudentService $studentService;

    public function __construct(
        TermService $termService,
        BreadcrumbService $breadcrumbService,
        CategoryService $categoryService,
        StudentService $studentService,
    ) {
        $this->termService = $termService;
        $this->breadcrumbService = $breadcrumbService;
        $breadcrumbService->addLevel('admin.terms.index', \trans('terms.title'));
        $this->categoryService = $categoryService;
        $this->studentService = $studentService;
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function backRoutes(): array
    {
        return [
            'list' => ['admin.terms.index', false],
            'show' => ['admin.terms.show', true],
        ];
    }

    /**
     * Get table column settings for AJAX DataTable response
     */
    public function getTableColumns(): TableColumns
    {
        $listQuery = $this->termService->getListQuery();

        $columns = (new TableColumns($listQuery))
            ->addColumn((new Column('flag'))->search(null))
            ->addColumn((new Column('flag', null, 'flag_icon'))
                ->onlyExtra()
                ->printCallback(static fn (Term $term) => \config('czechitas.flags.' . ($term->flag ?: 'default'))))
            ->addColumn(
                (new Column('start'))->jsonAlias('term_range')
                    ->dateSearch('%d.%m.%Y')
                    ->printCallback(
                        static fn (Term $term) => $term->term_range,
                    )->orderCallback(static function (Builder $query, $dir): void {
                        /** @var Builder<Term> $query */
                        $query->orderBy('start', $dir)->orderBy('end', $dir)->orderBy('id', $dir);
                    }),
            )
            ->addColumn((new Column('end'))->onlyExtra()->notInData())
            ->addColumn((new Column('category.name'))->jsonAlias('category')->noOrder())
            ->addColumn(
                // Added to print out
                (new RelationCountColumn(
                    'students',
                    'logged_in_students',
                    'students',
                ))->constraint(
                    static function (Builder $query): void {
                        /** @var Builder<Student> $query */
                        $query->loggedOut(false)->canceled(false);
                    },
                ),
            )
            ->addColumn(
                // Added for optimization for term policy
                (new RelationCountColumn('students'))->notInData()
                    ->onlyExtra()
                    ->constraint(static function (Builder $query): void {
                        /** @var Builder<Student> $query */
                        $query->canceled(false);
                    }),
            )
            ->addPolicies();

        return $columns;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('list', Term::class);

        $columnNames = DataTable::getJSColumnsList($this->getTableColumns());

        return \view('admin.terms.list', \compact('columnNames'));
    }

    /**
     * AJAX listing in Datatables
     *
     * @return array<string, mixed>
     */
    public function ajaxList(Request $request): array
    {
        $this->authorize('list', Term::class);

        return (new DataTable($request, $this->getTableColumns()))->getData();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Term::class);
        $categories = $this->categoryService->getHTMLSelectQuery()->get();
        $this->breadcrumbService->addLevelWithUrl('', \trans('app.actions.create'));

        return \view('admin.terms.create', \compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTermRequest $request): RedirectResponse
    {
        $this->authorize('create', Term::class);

        $term = $this->termService->insert($request->getData());
        Alert::success(\trans('terms.success.flash_create', ['date' => $term->term_range]))->flash();

        return \redirect()->route('admin.terms.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Term $term): View
    {
        $this->authorize('view', $term);
        $this->addBreadcrumb($term);
        $students = $this->studentService->getListForTerm($term)->get();

        return \view('admin.terms.show', \compact('term', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Term $term): View
    {
        $this->authorize('update', $term);
        $this->addBreadcrumb($term)->addLevelWithUrl('', \trans('app.actions.edit'));

        $categories = $this->categoryService->getHTMLSelectQuery()->get();

        return \view('admin.terms.edit', \compact('term', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTermRequest $request, Term $term): RedirectResponse
    {
        $this->authorize('update', $term);
        $this->termService->setContext($term)->update($request->getData());

        Alert::success(\trans('terms.success.flash_update', ['date' => $term->term_range]))->flash();

        return $this->redirectBack($request, [$term]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array{title: string, text: string}
     */
    public function updateFlag(UpdateFlagRequest $request, Term $term): array
    {
        $this->authorize('update', $term);
        $this->termService->setContext($term)->flagUpdate($request->getData());

        return [
            'title' => \trans('app.change_flag.success_title'),
            'text'  => \trans('app.change_flag.success_text'),
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Term $term): RedirectResponse
    {
        $this->authorize('delete', $term);
        $this->termService->setContext($term)->delete();

        Alert::success(\trans('terms.success.flash_delete', ['date' => $term->term_range]))->flash();

        return \redirect()->route('admin.terms.index');
    }

    protected function addBreadcrumb(Term $term): BreadcrumbService
    {
        return $this->breadcrumbService->addLevel('admin.terms.show', $term->term_range, [$term]);
    }
}
