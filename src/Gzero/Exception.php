<?php namespace Gzero;

class Exception extends \Exception {

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request Request object
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if (str_is('api.*', $request->getHost()) || $request->wantsJson()) {
            return response()->json(['message' => $this->getMessage()], 400);
        }
        return abort(500, '<strong>Whoops!</strong> Something went wrong!');
    }

}
