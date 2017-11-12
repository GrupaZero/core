<?php namespace Gzero\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @SWG\Swagger(
 *   schemes={"https"},
 *   basePath="/v1",
 *   host="api.dev.gzero.pl",
 *   consumes={"application/json"},
 *   produces={"application/json"},
 *   @SWG\Info(
 *     title="GZERO API",
 *     version="1.0.0"
 *   )
 * )
 */

/**
 * @SWG\SecurityScheme(
 *     securityDefinition="Auth",
 *     type="apiKey",
 *     description="Bearer token",
 *     name="Authorization",
 *     in="header"
 *   )
 * @SWG\SecurityScheme(
 *     securityDefinition="AdminAccess",
 *     type="apiKey",
 *     description="Bearer token",
 *     name="Authorization",
 *     in="header"
 *   )
 */

/**
 * @SWG\Tag(
 *   name="language",
 *   description="Everything about app languages"
 *   )
 * ),
 * @SWG\Tag(
 *   name="user",
 *   description="Everything about app users"
 *   ),
 * @SWG\Tag(
 *   name="options",
 *   description="Everything about app options"
 *   )
 * @SWG\Tag(
 *   name="public",
 *   description="Actions that do not require authentication."
 *   )
 * )
 */

/**
 * @SWG\Definition(
 *   definition="ValidationErrors",
 *   type="object",
 *   @SWG\Property(
 *     property="message",
 *     type="string",
 *     example="The given data was invalid."
 *   ),
 *   @SWG\Property(
 *     property="errors",
 *     type="array",
 *     @SWG\Items(ref="#/definitions/ValidationError")
 *   )
 * )
 * @SWG\Definition(
 *   definition="ValidationError",
 *   type="object",
 *   @SWG\Property(
 *     property="field_name",
 *     type="string",
 *     example="Validation message."
 *   )
 * )
 * @SWG\Definition(
 *   definition="ServerError",
 *   type="object",
 *   @SWG\Property(
 *     property="message",
 *     type="string",
 *     example="Server Error"
 *   )
 * )
 * @SWG\Definition(
 *   definition="BadRequestError",
 *   type="object",
 *   @SWG\Property(
 *     property="message",
 *     type="string",
 *     example="Bad Request"
 *   )
 * )
 */
class ApiController extends Controller {

    use AuthorizesRequests;

    /**
     * Return response in json format
     *
     * @param mixed $data    Response data
     * @param int   $code    Response code
     * @param array $headers HTTP headers
     *
     * @return JsonResponse
     */
    protected function respond($data, $code, array $headers = [])
    {
        return new JsonResponse($data, $code, array_merge($this->defaultHeaders(), $headers));
    }

    /**
     * Return no content response in json format
     *
     * @param array $headers HTTP headers
     *
     * @return JsonResponse
     */
    protected function successNoContent(array $headers = [])
    {
        return $this->respond(null, SymfonyResponse::HTTP_NO_CONTENT, $headers);
    }

    /**
     * Return server error response in json format
     *
     * @param string $message Custom error message
     * @param array  $headers HTTP headers
     *
     * @throws HttpException
     *
     * @return mixed
     */
    protected function errorBadRequest($message = 'Bad Request', array $headers = [])
    {
        return abort(SymfonyResponse::HTTP_BAD_REQUEST, $message, $headers);
    }

    /**
     * Return not found response in json format
     *
     * @param array $headers HTTP headers
     *
     * @throws NotFoundHttpException
     *
     * @return mixed
     */
    protected function errorNotFound(array $headers = [])
    {
        return abort(SymfonyResponse::HTTP_NOT_FOUND, 'Not found', $headers);
    }

    /**
     * Default headers for api response
     *
     * @return array
     */
    protected function defaultHeaders()
    {
        return [];
    }
}
