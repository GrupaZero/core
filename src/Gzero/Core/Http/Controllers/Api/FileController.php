<?php namespace Gzero\Core\Http\Controllers\Api;

use Gzero\Core\Jobs\CreateFile;
use Gzero\Core\Jobs\DeleteFile;
use Gzero\Core\Jobs\UpdateFile;
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
 * Class FileController
 *
 * @SWG\Tag(
 *   name="files",
 *   description="Everything about app files"
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
     * @param FileReadRepository $repository File repository
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
        $results->setPath(apiUrl('files'));

        return new FileCollection($results);
    }

    /**
     * Display a specified file.
     *
     * @SWG\Get(
     *   path="/files/{id}",
     *   tags={"files"},
     *   summary="Returns a specific file by id",
     *   description="Returns a specific file by id",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of file that needs to be returned.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/File"),
     *  ),
     *   @SWG\Response(response=404, description="File not found")
     * )
     *
     * @param int $id content Id
     *
     * @return FileResource
     */
    public function show($id)
    {
        $file = $this->repository->getById($id);

        if (!$file) {
            return $this->errorNotFound();
        }

        $this->authorize('read', $file);
        return new FileResource($file);
    }

    /**
     * Stores newly created file in database.
     *
     * @SWG\Post(path="/files",
     *   tags={"files"},
     *   summary="Stores newly created file",
     *   description="Stores newly created file",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to create.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"type, title, language_code, file"},
     *       @SWG\Property(property="type", type="string", example="image"),
     *       @SWG\Property(property="title", type="string", example="Example title"),
     *       @SWG\Property(property="language_code", type="string", example="en"),
     *       @SWG\Property(property="file", type="file"),
     *       @SWG\Property(property="info", type="array", example="{'key':'value'}", @SWG\Items(type="string")),
     *       @SWG\Property(property="is_active", type="boolean", example="true"),
     *       @SWG\Property(property="description", type="string", example="Example description"),
     *       @SWG\Property(property="custom_fields", type="array", example="{'key':'value'}", @SWG\Items(type="string")),
     *     )
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/File"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  )
     * )
     *
     * @param Request $request Uploaded file
     *
     * @return FileResource
     */
    public function store(Request $request)
    {
        $this->authorize('create', File::class);

        $input = $this->validator->validate('create');

        $author   = auth()->user();
        $title    = array_get($input, 'title');
        $file     = $request->file('file');
        $language = language(array_get($input, 'language_code'));
        $data     = array_except($input, ['title', 'language_code']);

        $file = dispatch_now(CreateFile::make($file, $title, $language, $author, $data));

        return new FileResource($this->repository->loadRelations($file));
    }

    /**
     * Updates the specified resource in the database.
     *
     * @param int $id File id
     *
     * @SWG\Patch(path="/files/{id}",
     *   tags={"files"},
     *   summary="Updates specified file",
     *   description="Updates specified file",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of file that needs to be updated.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to update.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="info", type="array", example="{'key':'value'}", @SWG\Items(type="string")),
     *       @SWG\Property(property="is_active", type="boolean", example="true")
     *     )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/File"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="File not found")
     * )
     *
     * @return FileResource
     */
    public function update($id)
    {
        $file = $this->repository->getById($id);

        if (!$file) {
            return $this->errorNotFound();
        }

        $this->authorize('update', $file);

        $input = $this->validator->validate('update');

        $file = dispatch_now(new UpdateFile($file, $input));

        return new FileResource($this->repository->loadRelations($file));
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/files/{id}",
     *   tags={"files"},
     *   summary="Deletes specified file",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of file that needs to be deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Successful operation"
     *   ),
     *   @SWG\Response(response=404, description="File not found")
     * )
     *
     * @param int $id File id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $file = $this->repository->getById($id);

        if (!$file) {
            return $this->errorNotFound();
        }

        $this->authorize('delete', $file);

        dispatch_now(new DeleteFile($file));

        return $this->successNoContent();
    }
}
