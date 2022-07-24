<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Admin;

use CzechitasApp\Http\Contracts\RedirectBack as RedirectBackContract;
use CzechitasApp\Http\Controllers\OrderController as NoAdminOrderController;
use CzechitasApp\Http\Requests\Order\UpdateOrderRequest;
use CzechitasApp\Http\Requests\UpdateFlagRequest;
use CzechitasApp\Http\Traits\RedirectBack;
use CzechitasApp\Models\Order;
use CzechitasApp\Modules\AjaxDataTables\Column;
use CzechitasApp\Modules\AjaxDataTables\DataTable;
use CzechitasApp\Modules\AjaxDataTables\TableColumns;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrderController extends NoAdminOrderController implements RedirectBackContract
{
    use RedirectBack;

    public function __construct(BreadcrumbService $breadcrumbService, OrderService $orderService)
    {
        parent::__construct($breadcrumbService, $orderService);

        $breadcrumbService->addLevel('admin.orders.index', \trans('orders.breadcrumbs.index'));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function backRoutes(): array
    {
        return [
            'list' => ['admin.orders.index', false],
            'show' => ['admin.orders.show', true],
        ];
    }

    /**
     * Get table column settings for AJAX DataTable response
     */
    public function getTableColumns(): TableColumns
    {
        $columns = (new TableColumns($this->orderService->getListQuery()))
            ->addColumn((new Column('flag'))->search(null))
            ->addColumn((new Column('flag', null, 'flag_icon'))
                ->onlyExtra()
                ->printCallback(static fn (Order $order) => \config('czechitas.flags.' . ($order->flag ?: 'default'))))
            ->addColumn((new Column('client')))
            ->addColumn((new Column('contact_name')))
            ->addColumn((new Column('signature_date'))->search(null)->printCallback(static function (Order $model) {
                if ($model->signature_date) {
                    return $model->signature_date->format('d.m.Y');
                }

                return null;
            }))
            ->addColumn(
                (new Column('type'))->search(null)->printCallback(
                    static fn (Order $model) => \trans('orders.type.short.' . $model->type)
                ),
            )
            ->addColumn((new Column('contact_tel'))->notInTable())
            ->addColumn((new Column('contact_mail'))->notInTable())
            ->addColumn((new Column('ico'))->notInData()->noOrder()) // search only
            ->addPolicies(['view', 'update', 'updateFlag', 'delete']);

        return $columns;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('list', Order::class);

        $columnNames = DataTable::getJSColumnsList($this->getTableColumns());

        return \view('admin.orders.list', \compact('columnNames'));
    }

    /**
     * AJAX listing in Datatables
     *
     * @return array<string, mixed>
     */
    public function ajaxList(Request $request): array
    {
        $this->authorize('list', Order::class);

        return (new DataTable($request, $this->getTableColumns()))->getData();
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $this->addBreadcrumb($order);

        return \view('admin.orders.show', \compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order): View
    {
        $this->authorize('update', $order);
        $this->addBreadcrumb($order)->addLevelWithUrl('', \trans('app.actions.edit'));

        return \view('admin.orders.edit', \compact('order'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array{title: string, text: string}
     */
    public function updateFlag(UpdateFlagRequest $request, Order $order): array
    {
        $this->authorize('updateFlag', $order);
        $this->orderService->setContext($order)->flagUpdate($request->getData());

        return [
            'title' => \trans('app.change_flag.success_title'),
            'text'  => \trans('app.change_flag.success_text'),
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->setContext($order)->update($request->getData());

        Alert::success(\trans('orders.success.flash'))->flash();

        return $this->redirectBack($request, [$order]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);
        $this->orderService->setContext($order)->delete();

        Alert::success(\trans('orders.success.flash_delete'))->flash();

        return \redirect()->route('admin.orders.index');
    }

    protected function addBreadcrumb(Order $order): BreadcrumbService
    {
        return $this->breadcrumbService->addLevel('admin.orders.show', \trans('orders.type.' . $order->type), [$order]);
    }
}
