<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Core\Models\File;
use Gzero\Core\Validators\FileValidator;
use Gzero\Core\Http\Resources\FileCollection;
use Gzero\Core\Http\Resources\File as FileResource;
use Gzero\Core\Repositories\FileReadRepository;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

/**
 * Class BlockController
 *
 * @SWG\Tag(
 *   name="blocks",
 *   description="Everything about app blocks"
 *   )
 */
class FileController extends ApiController {

    /** @var FileReadRepository */
    protected $repository;

    /** @var FileValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * FileController constructor.
     *
     * @param FileReadRepository $repository Block repository
     * @param FileValidator      $validator  Content validator
     * @param Request            $request    Request object
     */
    public function __construct(FileReadRepository $repository, FileValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/files",
     *   tags={"files"},
     *   summary="List of all files",
     *   description="List of all available files",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="Type to filter by",
     *     required=false,
     *     type="string",
     *     default="basic"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Name to filter by",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="extension",
     *     in="query",
     *     description="Extension to filter by",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="mime_type",
     *     in="query",
     *     description="Mime type to filter by",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="Size in bytes to filter by",
     *     required=false,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="author_id",
     *     in="query",
     *     description="Author id to filter by",
     *     required=false,
     *     type="integer",
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     name="is_active",
     *     in="query",
     *     description="Active files filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="created_at",
     *     in="query",
     *     description="Date range to filter by",
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     default={"2017-10-01","2017-10-07"},
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="updated_at",
     *     in="query",
     *     description="Date range to filter by",
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     default={"2017-10-01","2017-10-07"},
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/File")),
     *  ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  )
     * )
     *
     * @param UrlParamsProcessor $processor Params processor
     *
     * @return FileCollection
     */
    public function index(UrlParamsProcessor $processor)
    {
        $this->authorize('readList', File::class);

        $processor
            ->addFilter(new StringParser('type'))
            ->addFilter(new StringParser('name'))
            ->addFilter(new StringParser('extension'))
            ->addFilter(new StringParser('mime_type'))
            ->addFilter(new NumericParser('size'))
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new BoolParser('is_active'))
            ->addFilter(new DateRangeParser('created_at'))
            ->addFilter(new DateRangeParser('updated_at'))
            ->process($this->request);

        $results = $this->repository->getMany($processor->buildQueryBuilder());
        $results->setPath(apiUrl('blocks'));

        return new FileCollection($results);
    }
}
