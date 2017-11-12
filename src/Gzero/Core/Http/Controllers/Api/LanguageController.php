<?php namespace Gzero\Core\Http\Controllers\Api;

use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Http\Resources\Language as LanguageResource;
use Gzero\Core\Http\Resources\LanguageCollection;
use Gzero\Core\Services\LanguageService;

class LanguageController extends ApiController {

    /** @var LanguageService */
    protected $langService;

    /**
     * LangController constructor
     *
     * @param LanguageService $lang Content repo
     */
    public function __construct(LanguageService $lang)
    {
        $this->langService = $lang;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/languages",
     *   tags={"language", "public"},
     *   summary="List of all languages",
     *   description="Retrieves a list of all available languages.",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Language")),
     *  )
     * )
     *
     * @return LanguageCollection
     */
    public function index()
    {
        return new LanguageCollection($this->langService->getAll());
    }

    /**
     * Display the specified resource.
     *
     * @SWG\Get(
     *   path="/languages/{code}",
     *   tags={"language", "public"},
     *   summary="Get specific language",
     *   description="Retrieve specific languages from database, by it's code.",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     description="Language code that need to be returned, e.g <b>'en'</b>.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Language"),
     *   ),
     *   @SWG\Response(response=404,description="Language not found")
     * )
     *
     * @param string $code Lang code
     *
     * @return LanguageResource
     */
    public function show($code)
    {
        $lang = $this->langService->getByCode($code);

        if (!$lang) {
            return $this->errorNotFound();
        }

        return new LanguageResource($lang);
    }

}
