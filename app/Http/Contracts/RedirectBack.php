<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Contracts;

use CzechitasApp\Models\BaseModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface RedirectBack
{
    /**
     * @param int|string|BaseModel|array<int|string, int|string|BaseModel> $parameters
     * @param array<string, int|string|BaseModel>                          $extraParams
     */
    public function redirectBack(
        Request $request,
        int|string|BaseModel|array $parameters,
        string $defaultBackRoute,
        array $extraParams = [],
    ): RedirectResponse;

    /**
     * @return array<string, array{string, bool}>
     */
    public function backRoutes(): array;
}
