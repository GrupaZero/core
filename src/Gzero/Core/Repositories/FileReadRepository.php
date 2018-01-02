<?php namespace Gzero\Core\Repositories;

use Gzero\Core\Models\File;
use Gzero\Core\Query\QueryBuilder;
use Gzero\InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as RawBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

class FileReadRepository implements ReadRepository {

    /** @var array */
    public static $loadRelations = [
        'author',
        'translations',
        'type'
    ];

    /**
     * Retrieve a file by given id
     *
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return $this->loadRelations(File::find($id));
    }

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     * @throws InvalidArgumentException
     */
    public function getMany(QueryBuilder $builder)
    {
        return $this->getManyFrom(File::query(), $builder);
    }

    /**
     * Eager load relations
     *
     * @param File|Collection $model Model or collection
     *
     * @return File|Collection
     */
    public function loadRelations($model)
    {
        return optional($model)->load(self::$loadRelations);
    }

    /**
     * @param Builder|RawBuilder $query   Eloquent query object
     * @param QueryBuilder       $builder Query builder
     *
     * @return LengthAwarePaginator
     * @throws InvalidArgumentException
     */
    protected function getManyFrom(Builder $query, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $query->with(self::$loadRelations);

        if ($builder->hasRelation('translations')) {
            if (!$builder->getFilter('translations.language_code')) {
                throw new InvalidArgumentException('Language code is required');
            }
            $query->join('file_translations as t', 'files.id', '=', 't.file_id');
            $builder->applyRelationFilters('translations', 't', $query);
            $builder->applyRelationSorts('translations', 't', $query);
        }

        if ($builder->hasFilter('type') || $builder->hasSort('type')) {
            $query->join('file_types as ft', 'files.type_id', '=', 'ft.id');
            optional($builder->getFilter('type'))->apply($query, 'ft', 'name');
            optional($builder->getSort('type'))->apply($query, 'ft', 'name');
        }

        $builder->applyFilters($query);
        $builder->applySorts($query);

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['files.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('files.id')->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }

    /**
     * Get all translations for specified file.
     *
     * @param File         $file    Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyTranslations(File $file, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $file->translations()->newQuery()->getQuery();

        $builder->applyFilters($query);
        $builder->applySorts($query);

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['file_translations.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('file_translations.id')->get()->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }
}
