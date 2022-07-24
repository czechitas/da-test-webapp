<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Traits;

use CzechitasApp\Models\BaseModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait RedirectBack
{
    /**
     * @param int|string|BaseModel|array<int|string, int|string|BaseModel> $parameters
     * @param array<string, int|string|BaseModel>                          $extraParams
     */
    public function redirectBack(
        Request $request,
        int|string|BaseModel|array $parameters,
        string $defaultBackRoute = 'show',
        array $extraParams = [],
    ): RedirectResponse {
        $routeName = $this->getUrlRouteBack($request) ?: $defaultBackRoute;
        [$backRoute, $addParameters] = $this->backRoutes()[$routeName];

        return \redirect()->route(
            $backRoute,
            \array_merge(
                $addParameters ? Arr::wrap($parameters) : [],
                $extraParams,
            ),
        );
    }

    public function getUrlRouteBack(Request $request): ?string
    {
        return $request->query('routeBack', null);
    }
}
