<?php namespace Gzero\Core\Http\Controllers\Api;

use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Http\Resources\User as UserResource;
use Gzero\Core\Http\Resources\UserCollection;
use Gzero\Core\Jobs\DeleteUser;
use Gzero\Core\Jobs\UpdateUser;
use Gzero\Core\Models\User;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\UrlParamsProcessor;
use Gzero\Core\Repositories\UserReadRepository;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\Validators\UserValidator;
use Illuminate\Http\Request;

class UserController extends ApiController {

    /** @var Request */
    protected $request;

    /** @var UserReadRepository */
    protected $repository;

    /** @var UserValidator */
    protected $validator;

    /**
     * UserController constructor.
     *
     * @param UserReadRepository $repository User repository
     * @param UserValidator      $validator  User validator
     * @param Request            $request    Request object
     */
    public function __construct(UserReadRepository $repository, UserValidator $validator, Request $request)
    {
        $this->request    = $request;
        $this->repository = $repository;
        $this->validator  = $validator->setData($request->all());
    }

    /**
     * Display list of all users
     *
     * @SWG\Get(
     *   path="/users",
     *   tags={"user"},
     *   summary="List of all users",
     *   description="List of all available users, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Valid email address to filter by",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Name to filter by",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="first_name",
     *     in="query",
     *     description="First name to filter by",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="last_name",
     *     in="query",
     *     description="Last name to filter by",
     *     required=false,
     *     type="string"
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
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/User")),
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return UserCollection
     */
    public function index(UrlParamsProcessor $processor)
    {
        $this->authorize('readList', User::class);

        $processor
            ->addFilter(new StringParser('email'), 'email')
            ->addFilter(new StringParser('name'))
            ->addFilter(new StringParser('first_name'))
            ->addFilter(new StringParser('last_name'))
            ->addFilter(new DateRangeParser('created_at'))
            ->process($this->request);

        $results = $this->repository->getMany($processor->buildQueryBuilder());
        $results->setPath(apiUrl('users'));

        return new UserCollection($results);
    }

    /**
     * Display the specified resource.
     *
     * @SWG\Get(
     *   path="/users/{id}",
     *   tags={"user"},
     *   summary="Get specific user",
     *   description="Returns a specific user by id, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of user that needs to be returned.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/User"),
     *   ),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param int $id user id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return UserResource
     */
    public function show($id)
    {
        $user = $this->repository->getById($id);

        if (!$user) {
            return $this->errorNotFound();
        }

        $this->authorize('read', $user);
        return new UserResource($user);
    }

    /**
     * Updates the specified resource.
     *
     * @SWG\Patch(path="/users/{id}",
     *   tags={"user"},
     *   summary="Updated specific user",
     *   description="Updates specified user, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be updated.",
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
     *       required={"email, name"},
     *       @SWG\Property(property="email", type="string", example="john.doe@example.com"),
     *       @SWG\Property(property="name", type="string", example="JohnDoe"),
     *       @SWG\Property(property="first_name", type="string", example="John"),
     *       @SWG\Property(property="last_name", type="string", example="Doe")
     *     )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/User"),
     *   ),
     *   @SWG\Response(response=404, description="User not found"),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  )
     * )
     *
     * @param int $id User id
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return UserResource
     */
    public function update($id)
    {
        $user = $this->repository->getById($id);

        if (!$user) {
            return $this->errorNotFound();
        }

        $this->authorize('update', $user);
        $input = $this->validator
            ->bind('name', ['user_id' => $user->id])
            ->bind('email', ['user_id' => $user->id])
            ->validate('update');
        $user  = dispatch_now(new UpdateUser($user, $input));
        return new UserResource($user);
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Patch(path="/users/me",
     *   tags={"user"},
     *   summary="Updated current user",
     *   description="Updates currently logged in user.",
     *   produces={"application/json"},
     *   security={{"Auth": {}}, {"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to update, <b>'password'</b> is not required, it must match <b>'password_confirmation'</b>.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"email, name"},
     *       @SWG\Property(property="email", type="string", example="john.doe@example.com"),
     *       @SWG\Property(property="name", type="string", example="JohnDoe"),
     *       @SWG\Property(property="first_name", type="string", example="John"),
     *       @SWG\Property(property="last_name", type="string", example="Doe"),
     *       @SWG\Property(property="password", type="string", example="secret"),
     *       @SWG\Property(property="password_confirmation", type="string", example="secret")
     *     )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/User"),
     *   ),
     *   @SWG\Response(response=404, description="User not found"),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  )
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return UserResource
     */
    public function updateMe()
    {
        if (!$this->request->has('password')) {
            $this->validator->setData($this->request->except(['password', 'password_confirmation']));
        }

        $user = $this->repository->getById($this->request->user()->id);

        if (!$user) {
            return $this->errorNotFound();
        }

        $this->authorize('update', $user);
        $input = $this->validator
            ->bind('name', ['user_id' => $user->id])
            ->bind('email', ['user_id' => $user->id])
            ->validate('updateMe');
        $user  = dispatch_now(new UpdateUser($user, $input));
        return new UserResource($user);
    }

    /**
     * Remove the specified user from database.
     *
     * @SWG\Delete(
     *   path="/users/{id}",
     *   tags={"user"},
     *   summary="Delete specific user",
     *   description="Deletes specified user, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=204, description="Successful operation"),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param int $id Id of the user
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = $this->repository->getById($id);

        if (!$user) {
            return $this->errorNotFound();
        }

        $this->authorize('delete', $user);
        dispatch_now(new DeleteUser($user));
        return $this->successNoContent();
    }

}
