<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Api;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\Api\Order\CreateOrderRequest;
use CzechitasApp\Models\Order;
use CzechitasApp\Services\Models\OrderService;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(CreateOrderRequest $request): Response
    {
        $this->authorize('create', Order::class);
        $order = $this->orderService->insert($request->getData());

        return \response()->json($order, Response::HTTP_CREATED);
    }
}
