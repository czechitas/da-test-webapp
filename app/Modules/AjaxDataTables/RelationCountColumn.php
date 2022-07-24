<?php

declare(strict_types=1);

namespace CzechitasApp\Modules\AjaxDataTables;

use Closure;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

class RelationCountColumn extends Column
{
    public ?Closure $constraintQuery = null;

    /**
     * Create new column of DataTable
     *
     * @param string      $relationToCount Name of relation to count as passed to withCount
     * @param string|null $alias           Name of column as it should be selected - AS in sql
     * @param string|null $jsonAlias       Name which should appear in final JSON
     */
    public function __construct(string $relationToCount, ?string $alias = null, ?string $jsonAlias = null)
    {
        parent::__construct($relationToCount, $alias, $jsonAlias);

        $this->alias = $alias ?? Str::snake($this->name . '_count');
        $this->search(null)->noOrder();
    }

    /**
     * Add closure to constraint query as passed to withCount
     */
    public function constraint(Closure $closure): self
    {
        $this->constraintQuery = $closure;

        return $this;
    }

    /**
     * Get name for select - can be DB::raw
     *
     * @param bool $finalName Get final name @see getFinalName
     */
    public function getSelect(bool $finalName = false): string|Expression
    {
        if ($finalName) {
            return $this->alias;
        }

        return "{$this->name} as {$this->alias}";
    }
}
