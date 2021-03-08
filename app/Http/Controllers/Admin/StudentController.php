<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Admin;

use CzechitasApp\Http\Contracts\RedirectBack as RedirectBackContract;
use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\Student\AddPaymentRequest;
use CzechitasApp\Http\Requests\Student\AdminCanceledStudentRequest;
use CzechitasApp\Http\Requests\Student\AdminCreateStudentRequest;
use CzechitasApp\Http\Requests\Student\AdminLogoutStudentRequest;
use CzechitasApp\Http\Requests\Student\AdminUpdateStudentRequest;
use CzechitasApp\Http\Traits\RedirectBack;
use CzechitasApp\Models\Category;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\Term;
use CzechitasApp\Modules\AjaxDataTables\Column;
use CzechitasApp\Modules\AjaxDataTables\DataTable;
use CzechitasApp\Modules\AjaxDataTables\TableColumns;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\StudentService;
use CzechitasApp\Services\Models\TermService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StudentController extends Controller implements RedirectBackContract
{
    use RedirectBack;

    /** @var StudentService */
    private $studentService;

    /** @var BreadcrumbService */
    private $breadcrumbService;

    /** @var TermService */
    private $termService;

    public function __construct(
        StudentService $studentService,
        BreadcrumbService $breadcrumbService,
        TermService $termService
    ) {
        $this->studentService = $studentService;
        $this->breadcrumbService = $breadcrumbService;
        $this->termService = $termService;

        $breadcrumbService->addLevel('admin.students.index', \trans('students.title'));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function backRoutes(): array
    {
        return [
            'list' => ['admin.students.index', false],
            'show' => ['admin.students.show', true],
        ];
    }

    /**
     * Get table column settings for AJAX DataTable response
     */
    public function getTableColumns(): TableColumns
    {
        $listQuery = $this->studentService->getListQuery();

        $columns = (new TableColumns($listQuery, true))
            ->addQueryCallback(static function (Builder $query): void {
                /** @var Builder<Student> $query */
                $query->withPaymentSum();
            })
            ->addColumn((new Column('surname', null, 'name'))->printCallback(static function (Student $student) {
                return "{$student->surname} {$student->forename}";
            })->orderCallback(static function (Builder $query, $dir): void {
                /** @var Builder<Student> $query */
                $query->orderBy('surname', $dir)->orderBy('forename', $dir);
            }))
            ->addColumn((new Column('forename'))->noOrder()->notInTable())
            ->addColumn((new Column('term.term_range'))->search(null))
            ->addColumn((new Column('term_id'))->onlyExtra())
            ->addColumn((new Column('payment'))
                ->search(null)
                ->printCallback(static function (Student $student, $value) {
                    return \trans('students.payments.' . $value ?? 'none');
                }))
            ->addColumn((new Column('price_to_pay'))
                ->noDB()
                ->printCallback(static function (Student $student, $price) {
                    return \formatPrice($price);
                }))
            ->addColumn((new Column('rowClass'))
                ->noDB()
                ->onlyExtra()
                ->printCallback(static function (Student $student) {
                    return \studentListClass($student);
                }))
            ->addColumn((new Column('parent_name'))->notInData()->noOrder()) // Do search
            // ->addColumn((new Column('birthday'))->notInData()->dateSearch('%d.%m.%Y')->noOrder())
            // ->addColumn((new Column('birthday'))->notInData()->dateSearch('%e.%c.%Y')->noOrder())
            ->addColumn((new Column('variable_symbol'))->notInData()->noOrder())
            ->addPolicies();

        return $columns;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('list', Student::class);

        $columnNames = DataTable::getJSColumnsList($this->getTableColumns());

        return \view('admin.students.list', \compact('columnNames'));
    }

    /**
     * AJAX listing in Datatables
     *
     * @return array<string, mixed>
     */
    public function ajaxList(Request $request): array
    {
        $this->authorize('list', Student::class);

        return (new DataTable($request, $this->getTableColumns()))->getData();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Student::class);
        /** @var Collection<Term> $terms */
        $terms = $this->termService->getTermsForAdmin()->orderBy('start')->get();
        $categories = [];
        /** @var Collection<Category> $category */
        foreach ($terms->sortBy('category.name')->groupBy('category_id') as $category) {
            $categories[] = $category->sortBy('start');
        }
        $this->breadcrumbService->addLevel('admin.students.create', \trans('students.breadcrumbs.create'));

        return \view('admin.students.create', \compact('categories', 'terms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminCreateStudentRequest $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $student = $this->studentService->insert($request->getData(true));

        Alert::success(\trans('students.success.flash_create', ['name' => $student->name]))->flash();

        return \redirect()->route('admin.students.show', [$student]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        $this->authorize('view', $student);
        $this->addBreadcrumb($student);

        return \view('admin.students.show', \compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        $this->authorize('update', $student);
        $this->addBreadcrumb($student)->addLevelWithUrl('', \trans('students.breadcrumbs.edit'));
        $bladeTemplates = \compact('student');
        if ($student->term->isPossibleAdminChangeTerm()) {
            $terms = $this->termService->getTermsForAdmin()->orderBy('start')->get();
            $categories = $terms->sortBy('category.name')->groupBy('category_id');
            $bladeTemplates += \compact('terms', 'categories');
        }

        return \view('admin.students.edit', $bladeTemplates);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminUpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);
        $this->studentService->setContext($student)->update($request->getData());

        Alert::success(\trans('students.success.flash_update', ['name' => $student->name]))->flash();

        if ($this->getUrlRouteBack($request) === 'term') {
            return \redirect()->route('admin.terms.show', $student->term);
        }

        return $this->redirectBack($request, $student);
    }

    public function addPayment(AddPaymentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('addPayment', $student);
        $this->studentService->setContext($student)->insertPayment($request->getData());
        Alert::success(\trans('students.success.flash_insert_payment', ['name' => $student->name]))->flash();

        return \redirect()->route('admin.students.show', $student);
    }

    public function logout(AdminLogoutStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('logout', $student);
        $this->studentService->setContext($student)->updateLogout($request->getData());
        Alert::success(
            \trans(
                'students.logout.flash_' . ($student->logged_out == null ? 'isnot' : $student->logged_out),
                ['name' => $student->name]
            )
        )->flash();

        return \redirect()->route('admin.students.show', $student);
    }

    public function cancel(AdminCanceledStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('logout', $student);
        $this->studentService->setContext($student)->updateCanceled($request->getData());
        Alert::success(
            \trans(
                'students.cancel.flash_' . ($student->canceled ? 'is' : 'isnot'),
                ['name' => $student->name]
            )
        )->flash();

        return \redirect()->route('admin.students.show', $student);
    }

    /**
     * Show grid with send emails
     */
    public function sendEmails(Student $student): View
    {
        $this->authorize('sendEmails', $student);
        $this->addBreadcrumb($student)->addLevelWithUrl('', \trans('students.breadcrumbs.send_emails'));

        return \view('admin.students.send_emails', \compact('student'));
    }

    protected function addBreadcrumb(Student $student): BreadcrumbService
    {
        return $this->breadcrumbService->addLevel('admin.students.show', $student->name, [$student]);
    }
}
