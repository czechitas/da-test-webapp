<?php

declare(strict_types=1);

namespace CzechitasApp\Modules\AjaxDataTables;

use Closure;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Column
{
    public ?string $relation = null;

    public string|Expression $name;

    public ?string $alias = null;

    public ?string $jsonAlias = null;

    public bool $fromDB = true;

    public bool $select = true;

    public bool $order = true;

    public bool $inTable = true;

    public bool $inData = true;

    public string|Closure|null $searchOperator = 'LIKE';

    public bool $searchExactly = false;

    public ?Closure $orderCallbackClosure = null;

    public ?Closure $beforePrintCallback = null;

    /**
     * Create new column of DataTable
     *
     * @param string|Expression $name      Name of model column or DB::raw
     * @param string|null       $alias     Name of column as it should be selected - AS in sql
     * @param string|null       $jsonAlias Name which should appear in final JSON
     */
    public function __construct(string|Expression $name, ?string $alias = null, ?string $jsonAlias = null)
    {
        if (Str::contains($name, '.')) {
            $parts = \explode('.', $name);
            $name = \array_pop($parts);
            $this->relation = \implode('.', $parts);
        } else {
            $this->relation = null;
        }
        $this->name = $name;
        $this->alias = $alias;
        $this->jsonAlias = $jsonAlias;
    }

    /**
     * Set JSON alias - name which should appear in final JSON
     *
     * @return static
     */
    public function jsonAlias(string $jsonAlias): static
    {
        $this->jsonAlias = $jsonAlias;

        return $this;
    }

    /**
     * Mark a column not selecting data from DB
     *
     * @return static
     */
    public function noDB(): static
    {
        $this->fromDB = false;

        return $this;
    }

    /**
     * Set the column shouldn't be selected
     *
     * @return static
     */
    public function noSelect(): static
    {
        $this->select = false;

        return $this;
    }

    /**
     * Result cannot be ordered by this column
     *
     * @return static
     */
    public function noOrder(): static
    {
        $this->order = false;

        return $this;
    }

    /**
     * Column will not be present in JS DataTable but only in final JSON
     *
     * @return static
     */
    public function notInTable(): static
    {
        $this->inTable = false;

        return $this;
    }

    /**
     * Columns will not be present in final data,
     * this set also inTable to false @see notInTable
     *
     * @return static
     */
    public function notInData(): static
    {
        $this->inData = false;
        $this->notInTable();

        return $this;
    }

    /**
     * Alias of search(null), noOrder() and notInTable()
     * Column is selected only to get data
     *
     * @return static
     */
    public function onlyExtra(): static
    {
        $this->search(null)->noOrder()->notInTable();

        return $this;
    }

    /**
     * Set search operator to be used or NULL to disable search in this column
     *
     * @return static
     */
    public function search(?string $operator): static
    {
        $this->searchOperator = $operator;

        return $this;
    }

    /**
     * Set search operator to format date before searching
     *
     * @return static
     */
    public function dateSearch(string $format): static
    {
        $this->searchOperator = function (Builder $q, $search) use ($format): void {
            $q->orWhereRaw('DATE_FORMAT(`' . $this->name . "`, '{$format}') LIKE ?", ["%{$search}%"]);
        };

        return $this;
    }

    /**
     * Set callback to be executed when ordering by this column
     *
     * @param  Closure $callback Callback - 1st param is query and second ASC/DESC
     * @return static
     */
    public function orderCallback(Closure $callback): static
    {
        $this->orderCallbackClosure = $callback;

        return $this;
    }

    /**
     * Set callback to be executed when printing column
     *
     * @param  Closure $callback First param is model and second value to be printed
     * @return static
     */
    public function printCallback(Closure $callback): static
    {
        $this->beforePrintCallback = $callback;

        return $this;
    }

    // ---------------------------------------

    /**
     * If column belongs to given relation
     */
    public function isRelation(?string $relation = null): bool
    {
        if (empty($relation)) {
            return empty($this->relation);
        }
        if (empty($this->relation)) {
            return false;
        }

        return Str::is($relation, $this->relation);
    }

    /**
     * Get final name under which is returned value from DB
     */
    public function getFinalName(): string|Expression
    {
        return $this->getSelect(true);
    }

    /**
     * Get name which is used in JSON
     */
    public function getJsonName(): ?string
    {
        if (empty($this->jsonAlias)) {
            return $this->getFinalName();
        }

        return $this->jsonAlias;
    }

    /**
     * Is column inteded to be selected
     */
    public function isSelectable(): bool
    {
        if (!$this->fromDB) {
            return false;
        }

        return $this->select;
    }

    public function isSimpleSelect(): bool
    {
        return empty($this->alias) && \is_string($this->name);
    }

    /**
     * Get name for select - can be DB::raw
     *
     * @param bool $finalName Get final name @see getFinalName
     */
    public function getSelect(bool $finalName = false): string|Expression
    {
        if (!empty($this->alias)) {
            if ($finalName) {
                return $this->alias;
            }

            return DB::raw("{$this->name} as `{$this->alias}`");
        }

        return $this->name;
    }

    /**
     * Get name of the column for WHERE clause
     */
    public function getWhereName(): string|Expression
    {
        if (!empty($this->alias)) {
            return DB::raw($this->name);
        }

        return $this->name;
    }

    /**
     * Get name of the column for ORDER BY clause
     */
    public function getOrderByName(): string|Expression
    {
        return $this->getSelect(true);
    }

    /**
     * Can be searched by this column
     */
    public function isSearchable(): bool
    {
        if (!$this->fromDB) {
            return false;
        }

        return !empty($this->searchOperator);
    }

    /**
     * Can be result ordered by this column
     */
    public function isOrderable(): bool
    {
        if (!$this->fromDB) {
            return false;
        }

        return $this->order;
    }
}
