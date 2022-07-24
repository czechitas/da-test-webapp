<?php

declare(strict_types=1);

namespace CzechitasApp\Modules\AjaxDataTables;

use Closure;
use CzechitasApp\Modules\AjaxDataTables\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class TableColumns
{
    protected Builder $model;

    /** @var array<Column> */
    protected array $columns;

    /** @var array<int|string, Closure> */
    protected array $queryCallback = [];

     /** @var bool Select all columns from DB instead of only needed */
    public bool $selectAllColumns;

    /**
     *
     * @param Builder|string $model            Pass builder or FQCN of model
     * @param bool           $selectAllColumns Select all columns from DB instead of only needed
     */
    public function __construct(Builder|string $model, bool $selectAllColumns = false)
    {
        if (\is_string($model)) {
            $model = $model::query();
        }
        $this->model = $model;
        $this->selectAllColumns = $selectAllColumns;
    }

    /**
     * Add column to the list
     */
    public function addColumn(Column $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * Add callback with query parameter before passing to Builder
     */
    public function addQueryCallback(Closure $callback, int|string|null $relation = null): self
    {
        $this->queryCallback[$relation ?? 0] = $callback;

        return $this;
    }

    /**
     * Add policies to the result data about given model
     *
     * @param array<string> $actions List of policies - default view, update, delete
     */
    public function addPolicies(array $actions = []): self
    {
        if (empty($actions)) {
            $actions = ['view', 'update', 'delete'];
        }
        $this->addColumn(
            (new Column('policy'))->onlyExtra()
                ->noDB()
                ->printCallback(static function ($model) use ($actions) {
                    $policies = [];
                    $user = Auth::user();
                    foreach ($actions as $action) {
                        $policies[$action] = $user->can($action, $model);
                    }

                    return $policies;
                }),
        );

        return $this;
    }

    /**
     * Get base query builder
     */
    public function getModel(): Builder
    {
        return $this->model;
    }

    /**
     * Get columns for the given relation null for the base
     *
     * @param  string|null $relation Pass null for the base model, * for all relations or name of relation
     * @return array<Column>
     */
    public function getRelationColumns(?string $relation = null): array
    {
        if ($relation === '*') {
            return $this->columns;
        }
        $columns = [];
        foreach ($this->columns as $column) {
            if ($column->isRelation($relation)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Return all relations which will be used
     *
     * @return array<string|int> Names of relations
     */
    public function getRelations(): array
    {
        $relations = [];
        foreach ($this->columns as $column) {
            if (!empty($column->relation) && !\in_array($column->relation, $relations)) {
                $relations[] = $column->relation;
            }
        }

        return $relations;
    }

    /**
     * Get columns to be selected
     *
     * @param  bool        $addId          Add ID column to the end
     * @param  string|null $relation       Of which model columns should be returned
     * @param  bool        $addNoDBColumns Return also columns that are not selecting from DB
     * @return array<Column>
     */
    public function getSelectColumns(bool $addId = false, ?string $relation = null, bool $addNoDBColumns = true): array
    {
        $columns = [];
        foreach ($this->getRelationColumns($relation) as $column) {
            if ($column->isSelectable() || $addNoDBColumns) {
                $columns[] = $column;
            }
        }
        if ($addId) {
            $columns[] = new Column($this->model->getModel()->getKeyName());
        }

        return $columns;
    }

    /**
     * Get columns to be filtered by WHERE clause
     *
     * @param  string|null $relation Of which model columns should be returned
     * @return array<Column>
     */
    public function getWhereColumns(?string $relation = null): array
    {
        $columns = [];
        foreach ($this->getRelationColumns($relation) as $column) {
            if ($column->isSearchable()) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Return n-th column which is from given related model
     *
     * @param string|null $relation Of which model columns should be returned
     */
    public function getNthVisibleTableColumn(int $index, ?string $relation = null): ?Column
    {
        $nthVisibleColumn = \collect($this->getSelectColumns(false, '*'))
            ->filter(static fn (Column $column) => $column->inTable)
            ->values()
            ->get($index);

        if (empty($nthVisibleColumn)) {
            return null;
        }
        if (!$nthVisibleColumn->isRelation($relation)) {
            return null;
        }

        return $nthVisibleColumn;
    }

    /**
     * Call set callbacks
     */
    public function callCallback(Builder|Relation $query, ?string $relation = null): void
    {
        $relation = $relation ?? 0;
        if (!isset($this->queryCallback[$relation])) {
            return;
        }

        ($this->queryCallback[$relation])($query);
    }
}
