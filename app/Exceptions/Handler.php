<?php

namespace App\Exceptions;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // You can log the exception or perform other actions here
            Log::channel('gateway')->error(
                sprintf(
                    'Error: %s :: %s in %s on line %d',
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        $status = $this->determineStatusCode($exception);
        $responseArray = $this->formatErrorResponse($exception, $status);

        return response()->json($responseArray, $status);
    }

    /**
     * Format the error response based on exception type.
     */
    protected function formatErrorResponse(Throwable $exception, int $status): array
    {
        $exceptionDetails = $this->getExceptionDetails($exception);

        $baseResponse = [
            'status' => $status,
            'source' => config('app.name') . ' API',
            'message' => $exception->getMessage(),
            'error' => [
                'type' => get_class($exception),
                'details' => $exceptionDetails,
            ],
        ];

        if ($exception instanceof AuthorizationException) {
            $baseResponse['source'] = 'Authorization API';
        } elseif ($exception instanceof ConnectException) {
            $baseResponse['source'] = ucfirst($exception->getRequest()->getUri()->getHost()) . ' API';
            $baseResponse['error']['details'] = ['connection' => $exception->getHandlerContext()['error']];
        } elseif ($exception instanceof ClientException || $exception instanceof ServerException) {
            $baseResponse['source'] = $exceptionDetails['source'];
            $baseResponse['message'] = $exceptionDetails['message'];
            $baseResponse['error'] = $exceptionDetails['error'];
        }

        return $baseResponse;
    }

    /**
     * Determine the appropriate status code for an exception.
     */
    protected function determineStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        } elseif ($exception instanceof ValidationException) {
            return Response::HTTP_UNPROCESSABLE_ENTITY;
        } elseif ($exception instanceof ModelNotFoundException) {
            return Response::HTTP_NOT_FOUND;
        } elseif ($exception instanceof RequestException) {
            return $exception->getResponse() ? $exception->getResponse()->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        } elseif ($exception instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof AuthorizationException) {
            return Response::HTTP_FORBIDDEN;
        }

        //...
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Extract details from the exception for the response.
     *
     * @return array
     */
    protected function getExceptionDetails(Throwable $exception)
    {
        if (method_exists($exception, 'errors')) {
            return $exception->errors();
        }
        if ($exception instanceof RequestException && $exception->hasResponse()) {
            return json_decode($exception->getResponse()->getBody()->getContents(), true);
        }

        return [];
    }
}
