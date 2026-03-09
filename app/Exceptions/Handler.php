<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e) && app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        // API exception handling
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions with consistent JSON responses.
     */
    protected function handleApiException(Request $request, Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception);
        $errors = $this->getErrors($exception);

        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        // Add debug information in development
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get the HTTP status code from the exception.
     */
    protected function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof ValidationException) {
            return 422;
        }

        return 500;
    }

    /**
     * Get the error message from the exception.
     */
    protected function getMessage(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return 'The given data was invalid.';
        }

        if ($exception instanceof NotFoundHttpException) {
            return 'Resource not found.';
        }

        if ($exception instanceof UnauthorizedHttpException) {
            return 'Unauthorized.';
        }

        if (config('app.debug')) {
            return $exception->getMessage();
        }

        return 'An error occurred while processing your request.';
    }

    /**
     * Get detailed errors from the exception.
     */
    protected function getErrors(Throwable $exception): array
    {
        if ($exception instanceof ValidationException) {
            return $exception->errors();
        }

        return [];
    }
}
