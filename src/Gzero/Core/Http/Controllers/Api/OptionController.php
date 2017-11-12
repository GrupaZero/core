<?php namespace Gzero\Core\Http\Controllers\Api;

use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Models\Option;
use Gzero\Core\Http\Resources\Option as OptionResource;
use Gzero\Core\Http\Resources\OptionCollection;
use Gzero\Core\Http\Resources\OptionCategoryCollection;
use Gzero\Core\Repositories\RepositoryValidationException;
use Gzero\Core\Services\OptionService;
use Gzero\Core\Validators\OptionValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OptionController extends ApiController {

    /** @var OptionService */
    protected $optionService;

    /** @var OptionValidator */
    protected $validator;

    /**
     * OptionController constructor
     *
     * @param OptionService   $option    Option repo
     * @param OptionValidator $validator validator
     * @param Request         $request   Request object
     */
    public function __construct(OptionService $option, OptionValidator $validator, Request $request)
    {
        $this->validator     = $validator->setData($request->all());
        $this->optionService = $option;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/options",
     *   tags={"options", "public"},
     *   summary="Get all option categories",
     *   description="Retrieves a list of all available option categories.",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/OptionCategory")),
     *  )
     * )
     *
     * @return OptionCategoryCollection
     */
    public function index()
    {
        return new OptionCategoryCollection($this->optionService->getCategories());
    }

    /**
     * Display all options from selected category.
     *
     * @SWG\Get(
     *   path="/options/{category}",
     *   tags={"options", "public"},
     *   summary="Get all options from selected category, returned as key, value pairs for each available language.",
     *   description="Retrieves a list of all available options from specified category.",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="category",
     *     in="path",
     *     description="Category key that need to be returned",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(
     *     response=400,
     *     description="Category not found",
     *     @SWG\Schema(ref="#/definitions/BadRequestError")
     *   )
     * )
     *
     * @param string $key option category key
     *
     * @return OptionCollection
     */
    public function show($key)
    {
        try {
            $option = $this->optionService->getOptions($key);
            return new OptionCollection($option);
        } catch (RepositoryValidationException $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Put(
     *   path="/options/{category}",
     *   tags={"options"},
     *   summary="Updates selected option within the given category",
     *   description="Updates specified option for the given category, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="category",
     *     in="path",
     *     description="Category key that the updated option belongs to",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="option",
     *     in="body",
     *     description="Option that we want to update with value for each available language..",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"key, value"},
     *       @SWG\Property(
     *         property="key",
     *         type="string",
     *         example="example_key"
     *       ),
     *       @SWG\Property(
     *         property="value",
     *         type="array",
     *         example="['en' => null,'pl' => null,'de' => null,'fr' => null]",
     *         @SWG\Items(type="string"),
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Option"),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Category not found",
     *     @SWG\Schema(ref="#/definitions/BadRequestError")
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  )
     * )
     *
     * @param string $categoryKey option category key
     *
     * @return OptionResource
     * @throws ValidationException
     *
     */
    public function update($categoryKey)
    {
        $input = $this->validator->validate('update');
        $this->authorize('update', [Option::class, $categoryKey]);
        try {
            $this->optionService->updateOrCreateOption($categoryKey, $input['key'], $input['value']);
            return new OptionResource($this->optionService->getOption($categoryKey, $input['key']));
        } catch (RepositoryValidationException $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
