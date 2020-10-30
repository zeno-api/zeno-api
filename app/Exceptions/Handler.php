<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Zeno\Http\Presenter\Presenter;

class Handler extends ExceptionHandler
{
    protected Presenter $presenter;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Throwable $exception
     *
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $exception
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof ValidationException) {
            return $this->invalidJson($request, $exception);
        }

        return $this->presenter->render(
            $request,
            $this->getHttpStatusCode($exception),
            $this->convertExceptionToArray($exception)
        );
    }

    protected function prepareException(Throwable $exception)
    {
        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        if ($exception instanceof ModelNotFoundException) {
            return new NotFoundHttpException($exception->getMessage(), $exception);
        }

        if ($exception instanceof AuthorizationException) {
            return new HttpException(403, $exception->getMessage());
        }

        if ($exception instanceof ValidationException && $exception->getResponse()) {
            return $exception->getResponse();
        }

        return $exception;
    }

    protected function convertExceptionToArray(Throwable $e): array
    {
        return config('app.debug') ? [
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => collect($e->getTrace())->map(
                function ($trace) {
                    return Arr::except($trace, ['args']);
                }
            )->all(),
        ] : [
            'message' => $e->getCode() || $this->isHttpException($e) ? $e->getMessage() : 'Something Error',
        ];
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return $this->presenter->render(
            $request,
            $exception->status,
            [
                'message' => Arr::first($exception->errors()),
                'errors'  => $exception->errors(),
            ]
        );
    }

    protected function getHttpStatusCode(Exception $e)
    {
        if ($this->isHttpException($e)) {
            return $e->getStatusCode();
        }

        if ($e->getCode() && $e->getCode() >= 400 && $e->getCode() < 600) {
            return $e->getCode();
        }

        return 500;
    }
}
