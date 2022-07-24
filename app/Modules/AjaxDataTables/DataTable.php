<?php

declare(strict_types=1);

namespace CzechitasApp\Modules\AjaxDataTables;

use Closure;
use CzechitasApp\Modules\AjaxDataTables\TableColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DataTable
{
    /**
     * Current AJAX request
     */
    protected Request $request;

    /**
     * All table columns and settings
     */
    protected TableColumns $columns;

    /**
     * Init ajax data table
     *
     * @param Request|mixed $request
     */
    public function __construct($request, TableColumns $columns)
    {
        $this->request = $request;
        $this->columns = $columns;
    }

    /**
     * Get formatted data for JS DataTable
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $recordsTotal = $this->columns->getModel()->count();
        $query = $this->getQuery();
        $recordsFiltered = $query->count();
        $data = $this->getStructuredData($query);

        $ret = [
            'draw'      => \intval($this->request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];

        return $ret;
    }

    /**
     * Get list of columns to be shown in JS DataTable
     *
     * @return array<string>
     */
    public static function getJSColumnsList(TableColumns $columns): array
    {
        $colNames = [];
        foreach ($columns->getSelectColumns(false, '*') as $column) {
            if ($column->inTable) {
                $colNames[] = $column->getJsonName();
            }
        }

        return $colNames;
    }

    /**
     * Build query according to request
     */
    protected function getQuery(): Builder
    {
        $query = $this->columns->getModel();

        // TODO: Solve deeper relations than 1 level
        $relations = $this->columns->getRelations();
        $addSelectColumns = [];
        foreach ($relations as $relation) {
            $query->with([
                $relation => function (Relation $q) use ($relation): void {
                    $this->select($q, $relation);
                },
            ]);
            $addSelectColumns[] = $query->getModel()->$relation()->getForeignKeyName();
        }

        $this->select($query, null, $addSelectColumns);
        $this->where($query);
        $this->order($query);

        return $query;
    }

    // -----------------------------------------------------------

    /**
     * Add to query all columns to be selected
     *
     * @param array<string> $extraColumns Extra columns to be selected
     */
    protected function select(Builder|Relation $query, ?string $relation = null, array $extraColumns = []): void
    {
        $columns = $this->columns->selectAllColumns ? ['*'] : [];
        $countColumns = [];
        foreach ($this->columns->getSelectColumns(true, $relation, false) as $column) {
            // No all columns -> must be listed OR is not simple select
            if ($column instanceof RelationCountColumn) {
                if (empty($column->constraintQuery)) {
                    $countColumns[] = $column->getSelect();
                } else {
                    $countColumns[$column->getSelect()] = $column->constraintQuery;
                }
            } elseif (!$this->columns->selectAllColumns || !$column->isSimpleSelect()) {
                $columns[] = $column->getSelect();
            }
        }
        $query->select(\array_unique(\array_merge($columns, $extraColumns)));
        if (!empty($countColumns)) {
            $query->withCount($countColumns);
        }
        $this->columns->callCallback($query, $relation);
    }

    /**
     * Add to query all where conditions
     */
    protected function where(Builder|Relation $query): void
    {
        $search = $this->request->input('search.value');
        if ($search === null || \strlen($search) <= 0) {
            return;
        }

        $query->where(function (Builder $query) use ($search): void {
            $this->innerWhere($query, $search);

            foreach ($this->columns->getRelations() as $relation) {
                $columnsToSearch = $this->columns->getWhereColumns($relation);
                if (empty($columnsToSearch)) {
                    continue;
                }
                $query->orWhereHas($relation, function (Builder $query) use ($relation, $search): void {
                    $query->where(function (Builder $query) use ($relation, $search): void {
                        $this->innerWhere($query, $search, $relation);
                    });
                });
            }
        });
    }

    /**
     * Add to query all where conditions in given relation
     */
    protected function innerWhere(Builder|Relation $query, string $search, ?string $relation = null): void
    {
        foreach ($this->columns->getWhereColumns($relation) as $column) {
            if ($column->searchOperator instanceof Closure) {
                ($column->searchOperator)($query, $search);
            } else {
                $query->orWhere(
                    $column->getWhereName(),
                    $column->searchOperator,
                    $this->formatSearchValue($search, $column),
                );
            }
        }
    }

    /**
     * Add to query all order by conditions
     */
    protected function order(Builder|Relation $query, ?string $relation = null): void
    {
        $orders = $this->request->input('order');
        if (empty($orders) || !\is_array($orders)) {
            return;
        }
        foreach ($orders as $order) {
            $dir = $order['dir'] === 'desc' ? 'desc' : 'asc';

            $columnToOrder = $this->columns->getNthVisibleTableColumn(\intval($order['column']), $relation);
            if (empty($columnToOrder) || !$columnToOrder->isOrderable()) {
                continue;
            }
            if ($columnToOrder->orderCallbackClosure instanceof Closure) {
                ($columnToOrder->orderCallbackClosure)($query, $dir);

                continue;
            }
            $query->orderBy($columnToOrder->getOrderByName(), $dir);
        }
    }

    /**
     * Add limit to query builder according to request
     */
    protected function limitQuery(Builder|Relation $query): Builder
    {
        return $query
            ->offset(\intval($this->request->input('start')))
            ->limit(\intval($this->request->input('length')));
    }

    /**
     * Format data to be returned to JS DataTable
     *
     * @return array<array<string, mixed>>
     */
    protected function getStructuredData(Builder|Relation $query): array
    {
        // Collection of all data
        $modelData = $this->limitQuery($query)->get();
        $columns = $this->columns->getSelectColumns(true, '*');

        $data = [];
        foreach ($modelData as $baseModel) {
            $record = [];
            foreach ($columns as $column) {
                if ($column->inData === false) {
                    continue;
                }
                $finalName = $column->getFinalName(); // Final name in the model
                $jsonName = $column->getJsonName(); // Name in JSON

                // Select related model
                $model = empty($column->relation) ? $baseModel : $baseModel->{$column->relation};
                $value = $model->{$finalName} ?? null;

                if ($column->beforePrintCallback instanceof Closure) {
                    $record[$jsonName] = ($column->beforePrintCallback)($model, $value);
                } else {
                    $record[$jsonName] = $value;
                }
            }
            $data[] = $record;
        }

        return $data;
    }

    /**
     * @param  mixed $searchValue
     * @return mixed
     */
    protected function formatSearchValue($searchValue, Column $column)
    {
        if (
            $column->searchExactly === false
            && \is_string($column->searchOperator)
            && Str::upper($column->searchOperator) === 'LIKE'
        ) {
            return "%{$searchValue}%";
        }

        return $searchValue;
    }
}
