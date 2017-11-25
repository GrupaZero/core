<?php namespace Gzero\Core\Repositories;

use Gzero\Core\Models\Route;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Gzero\Core\Query\QueryBuilder;

class RouteReadRepository implements ReadRepository {

    /**
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return Route::find($id);
    }

    /**
     * @param string $path         URI path
     * @param string $languageCode Language code
     * @param bool   $onlyActive   Trigger
     *
     * @return Route|mixed
     */
    public function getByPath(string $path, string $languageCode, bool $onlyActive = false)
    {
        return Route::query()
            ->where('language_code', $languageCode)
            ->where('path', $path)
            ->when($onlyActive, function ($query) {
                $query->where('is_active', true);
            })
            ->first();
    }

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @throws RepositoryValidationException
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getMany(QueryBuilder $builder)
    {
        $query = Route::query();

        $builder->applyFilters($query);
        $builder->applySorts($query);

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['routes.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('routes.id')->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }
}
