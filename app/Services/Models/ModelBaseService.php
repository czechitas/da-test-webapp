<?php

declare(strict_types=1);

namespace CzechitasApp\Services\Models;

use CzechitasApp\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

abstract class ModelBaseService
{
    protected ?BaseModel $context = null;

    /**
     * Get FQCN category model
     */
    abstract public function getModel(): string;

    /**
     * Get model query builder
     */
    public function getQuery(): Builder
    {
        return $this->getModel()::query();
    }

    /**
     * Set context model
     *
     * @return static
     */
    public function setContext(BaseModel $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get current service context
     */
    public function getContext(): BaseModel
    {
        return $this->context;
    }
}
