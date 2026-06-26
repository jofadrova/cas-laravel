<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ScasTable
{


    protected Builder $query;
    protected Request $request;

    protected array $searchable = [];
    protected string $defaultSort = 'id';
    protected string $defaultDirection = 'asc';

    protected array $filters = [];
    protected array $sortable = [];
    protected $customSearch = null;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->request = request();
    }

    public static function make(Builder $query): self
    {
        return new self($query);
    }

    public function search(array $fields): self
    {
        $this->searchable = $fields;
        $buscar = trim($this->request->get('buscar', ''));
        if ($this->customSearch) {
            return $this;
        }

        if ($buscar !== '') {
            $this->query->where(function ($q) use ($buscar) {
                foreach ($this->searchable as $field) {
                    $q->orWhere($field, 'LIKE', "%{$buscar}%");
                }
            });
        }
        return $this;
    }

    public function customSearch(callable $callback): self
    {
        $this->customSearch = $callback;

        $buscar = trim($this->request->get('buscar', ''));

        if ($buscar !== '') {

            call_user_func(
                $this->customSearch,
                $this->query,
                $buscar
            );

        }

        return $this;
    }

   public function defaultSort(string $column, string $direction = 'asc'): self
    {
        $sort = $this->request->get('sort', $column);

        $direction = strtolower(
            $this->request->get('direction', $direction)
        );

        if (!in_array($direction, ['asc', 'desc'])) {

            $direction = 'asc';

        }

        /*
        * Seguridad:
        * solamente se permitirá ordenar
        * por columnas registradas.
        */

        if (!empty($this->sortable)) {

            if (!in_array($sort, $this->sortable)) {

                $sort = $column;

            }

        }

        $this->query->orderBy($sort, $direction);

        return $this;
    }

    public function currentSort(): string
    {
        return $this->request->get('sort', '');
    }

    public function currentDirection(): string
    {
        return strtolower(
            $this->request->get('direction', 'asc')
        );
    }

    public function paginate(int $rows = 10)
    {
        return $this->query
            ->paginate($rows)
            ->withQueryString();
    }

   public function filters(array $fields): self
    {
        $this->filters = $fields;
        foreach ($fields as $field) {
            $valor = $this->request->get($field);
            if (filled($valor)) {
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field);
                    $this->query->whereHas($relation, function ($q) use ($column, $valor) {
                        $q->where($column, $valor);
                    });
                } else {
                    $this->query->where($field, $valor);
                }
            }
        }
        return $this;
    }
    public function sortable(array $columns): self
    {
        $this->sortable = $columns;

        return $this;
    }

    public function nextDirection(string $column): string
    {
        if (
            $this->currentSort() === $column &&
            $this->currentDirection() === 'asc'
        ) {
            return 'desc';
        }

        return 'asc';
    }

    public function sortUrl(string $column): string
    {
        return request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $this->nextDirection($column),
        ]);
    }

    public function sortIcon(string $column): string
    {
        if ($this->currentSort() !== $column) {
            return 'fa-sort';
        }

        return $this->currentDirection() === 'asc'
            ? 'fa-sort-up'
            : 'fa-sort-down';
    }


}
