<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Admin;

use CzechitasApp\Http\Contracts\RedirectBack as RedirectBackContract;
use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\User\CreateUserRequest;
use CzechitasApp\Http\Requests\User\UpdateUserRequest;
use CzechitasApp\Http\Traits\RedirectBack;
use CzechitasApp\Models\User;
use CzechitasApp\Modules\AjaxDataTables\Column;
use CzechitasApp\Modules\AjaxDataTables\DataTable;
use CzechitasApp\Modules\AjaxDataTables\TableColumns;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\StudentService;
use CzechitasApp\Services\Models\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;

class UserController extends Controller implements RedirectBackContract
{
    use RedirectBack;

    private BreadcrumbService $breadcrumbService;

    private UserService $userService;

    private StudentService $studentService;

    public function __construct(
        BreadcrumbService $breadcrumbService,
        UserService $userService,
        StudentService $studentService,
    ) {
        $this->breadcrumbService = $breadcrumbService;
        $this->userService = $userService;
        $this->studentService = $studentService;

        $breadcrumbService->addLevel('admin.users.index', \trans('users.title'));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function backRoutes(): array
    {
        return [
            'list' => ['admin.users.index', false],
            'show' => ['admin.users.show', true],
        ];
    }

    public function getTableColumns(): TableColumns
    {
        $columns = (new TableColumns($this->userService->getListQuery()))
            ->addColumn((new Column('name')))
            ->addColumn((new Column('email')))
            ->addColumn(
                (new Column('role'))->search(null)->printCallback(
                    static fn (User $model) => \trans('users.role.' . $model->role)
                ),
            )
            ->addColumn((new Column('is_blocked'))->onlyExtra())
            ->addPolicies(['view', 'update', 'delete', 'unblock']);

        return $columns;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('list', User::class);

        $columnNames = DataTable::getJSColumnsList($this->getTableColumns());

        return \view('admin.users.list', \compact('columnNames'));
    }

    /**
     * AJAX listing in Datatables
     *
     * @return array<string, mixed>
     */
    public function ajaxList(Request $request): array
    {
        $this->authorize('list', User::class);

        return (new DataTable($request, $this->getTableColumns()))->getData();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', User::class);
        $this->breadcrumbService->addLevel('admin.users.create', \trans('users.breadcrumbs.create'));

        return \view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->insert($request->getData());
        Alert::success(\trans('users.success.flash_create', ['name' => $user->name]))->flash();

        return \redirect()->route('admin.users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);
        $this->addBreadcrumb($user);
        $students = $this->studentService->getListForParent($user)->get();

        return \view('admin.users.show', \compact('user', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        $this->addBreadcrumb($user)->addLevelWithUrl('', \trans('app.actions.edit'));

        return \view('admin.users.edit', \compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $this->userService->setContext($user)->update($request->getData());

        Alert::success(\trans('users.success.flash_update', ['name' => $user->name]))->flash();

        return $this->redirectBack($request, [$user]);
    }

    /**
     * Unblock the specified resource in storage.
     */
    public function block(User $user): RedirectResponse
    {
        $this->authorize('block', $user);
        $this->userService->setContext($user)->block();

        Alert::success(\trans('users.success.flash_block', ['name' => $user->name]))->flash();

        return \redirect()->route('admin.users.index');
    }

    /**
     * Unblock the specified resource in storage.
     */
    public function unblock(User $user): RedirectResponse
    {
        $this->authorize('unblock', $user);
        $this->userService->setContext($user)->unblock();

        Alert::success(\trans('users.success.flash_unblock', ['name' => $user->name]))->flash();

        return \redirect()->route('admin.users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(User $user): View
    {
        $this->authorize('delete', $user);
        $constraints = $this->userService->setContext($user)->getConstraints();
        $possibleDelete = $this->userService->isDeletePossible($constraints);

        $this->addBreadcrumb($user)->addLevelWithUrl('', \trans('app.actions.destroy'));

        return \view('admin.users.delete', \compact('user', 'constraints', 'possibleDelete'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        if (!$this->userService->setContext($user)->isDeletePossible()) {
            \abort(403);
        }
        $this->userService->setContext($user)->delete();

        Alert::success(\trans('users.success.flash_delete', ['name' => $user->name]))->flash();

        return \redirect()->route('admin.users.index');
    }

    protected function addBreadcrumb(User $user): BreadcrumbService
    {
        return $this->breadcrumbService->addLevel('admin.users.show', $user->name, [$user]);
    }
}
