<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

use Illuminate\Contracts\Support\Arrayable;

class BreadcrumbService implements Arrayable
{
    /** @var array<array<string, mixed>> */
    private array $breadcrumbs = [];

    public function __construct(?string $route = null, ?string $text = null)
    {
        $this->addLevel($route, $text ?? \trans('app.breadcrumbs.home'));
    }

    /**
     * @param mixed $params
     */
    public function addLevel(?string $routeName, ?string $text, $params = []): self
    {
        return $this->addLevelWithUrl(\route($routeName ?? 'home', $params), $text);
    }

    public function addLevelWithUrl(string $route, string $text): self
    {
        $this->breadcrumbs[] = [
            'route' => $route,
            'text'  => $text,
        ];

        return $this;
    }

    /**
     * @param mixed $params
     */
    public function fresh(?string $routeName, ?string $text, $params = []): self
    {
        $this->breadcrumbs = [];

        return $this->addLevel($routeName, $text, $params);
    }

    public function isActive(int $minLevels = 2): bool
    {
        return \count($this->breadcrumbs) >= $minLevels;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    /**
     * Get the instance as an array.
     *
     * @return array<array<string, mixed>>
     */
    public function toArray(): array
    {
        return $this->getBreadcrumbs();
    }
}
