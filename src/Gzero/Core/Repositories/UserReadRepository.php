<?php namespace Gzero\Core\Repositories;

use Gzero\Core\Models\User;
use Gzero\Core\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserReadRepository implements ReadRepository {

    /**
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return User::find($id);
    }

    /**
     * Retrieve a user by given email
     *
     * @param  string $email User email
     *
     * @return User|mixed
     */
    public function getByEmail($email)
    {
        return User::query()->where('email', '=', $email)->first();
    }

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return LengthAwarePaginator|Collection
     */
    public function getMany(QueryBuilder $builder)
    {
        $query = User::query();

        $builder->applyFilters($query);
        $builder->applySorts($query);

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['users.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('users.id')->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }
}
