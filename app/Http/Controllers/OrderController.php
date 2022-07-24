<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers;

use CzechitasApp\Http\Requests\Order\CreateOrderRequest;
use CzechitasApp\Services\AresService;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\Models\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    protected BreadcrumbService $breadcrumbService;

    protected OrderService $orderService;

    public function __construct(BreadcrumbService $breadcrumbService, OrderService $orderService)
    {
        $this->breadcrumbService = $breadcrumbService;
        $this->orderService = $orderService;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->breadcrumbService->addLevel('orders.create', \trans('orders.breadcrumbs.create'));

        return \view(
            \session()->has('orderStored') ? 'orders.success' : 'orders.create',
        );
    }

    /**
     * Redirect to create
     */
    public function createRedirect(): Response
    {
        return \redirect()->route('orders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOrderRequest $request): Response
    {
        $this->orderService->insert($request->getData());

        Alert::success(\trans('orders.success.flash'))->flash();

        return \back()->with('orderStored', 'yes');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function fromAres(Request $request, AresService $aresService): Response
    {
        $data = $aresService->loadCompanyInfoByICO($request->input('ico'));

        if (empty($data)) {
            return \response('Error', $data === null ? 404 : 500);
        }

        return \response($data);
    }
}
