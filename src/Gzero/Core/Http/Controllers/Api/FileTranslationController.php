<?php namespace Gzero\Core\Http\Controllers\Api;

use Gzero\Core\Http\Resources\FileTranslationCollection;
use Gzero\Core\Jobs\AddFileTranslation;
use Gzero\Core\Jobs\DeleteFileTranslation;
use Gzero\Core\Models\File;
use Gzero\Core\Repositories\FileReadRepository;
use Gzero\Core\Validators\FileTranslationValidator;
use Gzero\Core\Http\Resources\FileTranslation as FileTranslationResource;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

class FileTranslationController extends ApiController {

    /** @var FileReadRepository */
    protected $repository;

    /** @var FileTranslationValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * ContentTranslationController constructor
     *
     * @param FileReadRepository       $repository File repository
     * @param FileTranslationValidator $validator  File validator
     * @param Request                  $request    Request object
     */
    public function __construct(FileReadRepository $repository, FileTranslationValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/files/{id}/translations",
     *   tags={"files"},
     *   summary="List of all file translations",
     *   description="List of all available file translations",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
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
     *     description="Active translation filter",
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
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/FileTranslation")),
     *  ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="File not found")
     * )
     *
     * @param UrlParamsProcessor $processor Params processor
     * @param int|null           $id        Id used for nested resources
     *
     * @return FileTranslationCollection
     */
    public function index(UrlParamsProcessor $processor, $id)
    {
        $file = $this->repository->getById($id);

        if (!$file) {
            return $this->errorNotFound();
        }

        $this->authorize('readList', File::class);

        $processor
            ->addFilter(new StringParser('language_code'), 'in:pl,en,de,fr')
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new BoolParser('is_active'))
            ->addFilter(new DateRangeParser('created_at'))
            ->addFilter(new DateRangeParser('updated_at'))
            ->process($this->request);

        $results = $this->repository->getManyTranslations($file, $processor->buildQueryBuilder());
        $results->setPath(apiUrl('files/{id}/translations', ['id' => $id]));

        return new FileTranslationCollection($results);
    }

    /**
     * Stores newly created translation for specified file entity in database.
     *
     * @SWG\Post(path="/files/{id}/translations",
     *   tags={"files"},
     *   summary="Stores newly created file translation",
     *   description="Stores newly created file translation",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to create.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"title, language_code"},
     *       @SWG\Property(property="language_code", type="string", example="en"),
     *       @SWG\Property(property="title", type="string", example="Example title"),
     *       @SWG\Property(property="description", type="string", example="Example description")
     *     )
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/FileTranslation"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="File not found")
     * )
     *
     * @param int $id Id of the content
     *
     * @return FileTranslationResource
     */
    public function store($id)
    {
        $file = $this->repository->getById($id);

        if (!$file) {
            return $this->errorNotFound();
        }

        $this->authorize('create', $file);

        $input = $this->validator->validate('create');

        $author   = auth()->user();
        $title    = array_get($input, 'title');
        $language = language(array_get($input, 'language_code'));
        $data     = array_except($input, ['title', 'language_code']);

        $translation = dispatch_now(new AddFileTranslation($file, $title, $language, $author, $data));

        return new FileTranslationResource($translation);
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/files/{id}/translations/{id}",
     *   tags={"files"},
     *   summary="Deletes specified file translation",
     *   description="Deletes specified file translation.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of file that holds translation.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="translationId",
     *     in="path",
     *     description="Id of file translation that needs to be deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Successful operation"
     *   ),
     *   @SWG\Response(response=404, description="File or file translation not found")
     * )
     *
     * @param int $id            File id
     * @param int $translationId Translation id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $translationId)
    {
        $file = $this->repository->getById($id);

        if (!$file) {
            return $this->errorNotFound();
        }

        $this->authorize('delete', $file);
        $translation = $file->translations(false)->find($translationId);

        if (!$translation) {
            return $this->errorNotFound();
        }

        dispatch_now(new DeleteFileTranslation($translation));

        return $this->successNoContent();
    }
}
