<?php

namespace App\Traits;

use App\Support\Table;
use Illuminate\Database\Eloquent\Builder;

trait HasTable
{
    protected function table(
        Builder $query,
        array $searchable = [],
        array $filters = [],
        string $defaultSort = 'id',
        int $perPage = 10
    ) {
        return Table::make($query)
            ->search($searchable)
            ->filters($filters)
            ->defaultSort($defaultSort)
            ->paginate($perPage);
    }
}