<?php namespace Gzero\Core\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler {

    /**
     * Render an exception into a response.
     *
     * @param \Illuminate\Http\Request $request Request
     * @param \Exception               $e       Exception
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if (str_is('api.*', $request->getHost())) {
            /** @TODO Really ugly hack to always return JSON for api */
            $request->headers->set('x-requested-with', 'XMLHttpRequest');
        }
        return parent::render($request, $e);
    }

    /**
     * Prepare exception for rendering.
     *
     * @param Exception $e exception
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return Exception
     */
    protected function prepareException(Exception $e)
    {
        $e = parent::prepareException($e);

        if ($e instanceof MethodNotAllowedHttpException) {
            $e = new HttpException(405, 'Method not allowed', $e->getPrevious(), $e->getHeaders(), $e->getCode());
        }

        return $e;
    }
}
