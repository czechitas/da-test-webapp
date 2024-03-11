<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers;

use CzechitasApp\Http\Contracts\RedirectBack as RedirectBackContract;
use CzechitasApp\Http\Requests\Student\CreateStudentRequest;
use CzechitasApp\Http\Requests\Student\LogoutStudentRequest;
use CzechitasApp\Http\Requests\Student\UpdateStudentRequest;
use CzechitasApp\Http\Traits\RedirectBack;
use CzechitasApp\Models\Category;
use CzechitasApp\Models\SendEmail;
use CzechitasApp\Models\Student;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\SendEmailService;
use CzechitasApp\Services\Models\StudentService;
use CzechitasApp\Services\Models\TermService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class StudentController extends Controller implements RedirectBackContract
{
    use RedirectBack;

    private StudentService $studentService;

    private BreadcrumbService $breadcrumbService;

    private TermService $termService;

    private SendEmailService $sendEmailService;

    public function __construct(
        StudentService $studentService,
        BreadcrumbService $breadcrumbService,
        TermService $termService,
        SendEmailService $sendEmailService,
    ) {
        $this->studentService = $studentService;
        $this->breadcrumbService = $breadcrumbService;
        $this->termService = $termService;
        $this->sendEmailService = $sendEmailService;
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function backRoutes(): array
    {
        return [
            'list' => ['students.index', false],
            'show' => ['students.show', true],
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('parentList', Student::class);
        $students = $this->studentService->getListForParent(Auth::user())->get();

        return \view('students.list', \compact('students'));
    }

    /**
     * Redirect to homepage with flash to select category
     */
    public function create(): Response
    {
        $this->authorize('create', Student::class);

        return \redirect()->route('home')->with('showIntroSelectHelp', true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createForm(Category $category): View
    {
        if ($category->parent_id === null) {
            \abort(404);
        }
        $this->authorize('create', Student::class);
        $terms = $this->termService->getTermsOfCategoryForParents($category)->get();
        $this->breadcrumbService->addLevel('students.create', \trans('students.breadcrumbs.create'));

        return \view('students.create', \compact('category', 'terms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateStudentRequest $request): Response
    {
        $this->authorize('create', Student::class);

        $student = $this->studentService->insert($request->getData(true));

        Alert::success(\trans('students.success.flash_create', ['name' => $student->name]))->flash();

        return $this->redirectBack($request, [$student]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        $this->authorize('view', $student);
        $this->addBreadcrumb($student);

        return \view('students.show', \compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        $this->authorize('update', $student);
        $this->addBreadcrumb($student)->addLevelWithUrl('', \trans('students.breadcrumbs.edit'));

        return \view('students.edit', \compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): Response
    {
        $this->authorize('update', $student);
        $this->studentService->setContext($student)->update($request->getData());

        Alert::success(\trans('students.success.flash_update', ['name' => $student->name]))->flash();

        return $this->redirectBack($request, [$student]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function logoutForm(Student $student): View
    {
        $this->authorize('logout', $student);
        $this->addBreadcrumb($student)->addLevelWithUrl('', \trans('students.breadcrumbs.logout'));

        return \view('students.logout', \compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function logout(LogoutStudentRequest $request, Student $student): Response
    {
        $this->authorize('logout', $student);
        $this->studentService->setContext($student)->logout($request->getData());

        Alert::success(\trans('students.success.flash_logout', ['name' => $student->name]))->flash();

        return $this->redirectBack($request, [$student]);
    }

    /**
     * Show grid with send emails
     */
    public function sendEmails(Student $student): View
    {
        sleep(10);
        $this->authorize('sendEmails', $student);
        $this->addBreadcrumb($student)->addLevelWithUrl('', \trans('students.breadcrumbs.send_emails'));

        return \view('students.send_emails', \compact('student'));
    }

    /**
     * Show send email content
     */
    public function showSendEmail(Student $student, SendEmail $sendEmail): Response
    {
        $this->authorize('sendEmails', $student);
        if ($sendEmail->student->id !== $student->id) {
            \abort(404);
        }
        $this->addBreadcrumb($student)->addLevelWithUrl('', \trans('students.breadcrumbs.send_emails'));

        $content = $this->sendEmailService->setContext($sendEmail)->getFileContent();
        if (empty($content)) {
            \abort(500);
        }

        return \response($content);
    }

    /**
     * Download student login certificate
     */
    public function certificateLogin(Student $student): void
    {
        $this->authorize('certificateLogin', $student);
        $this->studentService
            ->setContext($student)
            ->certificateLogin()
            ->download(\trans('students.certificates.login_file') . '.pdf');

        exit;
    }

    /**
     * Download student payment certificate
     */
    public function certificatePayment(Student $student): void
    {
        $this->authorize('certificatePayment', $student);
        $this->studentService
            ->setContext($student)
            ->certificatePayment()
            ->download(\trans('students.certificates.payment_file') . '.pdf');

        exit;
    }

    public function destroy(): void
    {
        \abort(403);
    }

    protected function addBreadcrumb(Student $student): BreadcrumbService
    {
        return $this->breadcrumbService
            ->addLevel('students.index', \trans('app.menu.students'))
            ->addLevel('students.show', $student->name, [$student]);
    }
}
