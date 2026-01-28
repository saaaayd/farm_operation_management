<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            //
        });

        // Handle API exceptions
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException(Throwable $e, Request $request)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error' => 'Authentication required',
            ], 401);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error' => 'The requested resource does not exist',
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'error' => 'The requested API endpoint does not exist',
            ], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error' => 'The HTTP method is not allowed for this endpoint',
            ], 405);
        }

        if ($e instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => 'HTTP error',
                'error' => $e->getMessage() ?: 'An HTTP error occurred',
            ], $e->getStatusCode());
        }

        // Handle general exceptions
        $statusCode = 500;
        $message = 'Internal server error';
        $error = 'An unexpected error occurred';

        if (config('app.debug')) {
            $error = $e->getMessage();
            $trace = $e->getTraceAsString();
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error,
            'trace' => $trace ?? null,
        ], $statusCode);
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error' => 'Authentication required',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Handle weather API exceptions specifically
     */
    protected function handleWeatherApiException(Throwable $e)
    {
        \Log::error('Weather API Error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Weather service temporarily unavailable',
            'error' => 'Unable to fetch weather data at this time',
            'fallback_data' => $this->getDefaultWeatherData(),
        ], 503);
    }

    /**
     * Handle marketplace exceptions
     */
    protected function handleMarketplaceException(Throwable $e)
    {
        \Log::error('Marketplace Error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Marketplace service error',
            'error' => 'Unable to process marketplace request',
        ], 500);
    }

    /**
     * Handle inventory exceptions
     */
    protected function handleInventoryException(Throwable $e)
    {
        \Log::error('Inventory Error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Inventory management error',
            'error' => 'Unable to process inventory request',
        ], 500);
    }

    /**
     * Get default weather data for fallback
     */
    protected function getDefaultWeatherData()
    {
        return [
            'temperature' => 25.0,
            'humidity' => 60,
            'pressure' => 1013,
            'wind_speed' => 10.0,
            'wind_direction' => 180,
            'conditions' => 'clear',
            'description' => 'Clear sky',
            'visibility' => 10000,
            'rain' => [],
            'clouds' => 20,
            'sunrise' => '06:00',
            'sunset' => '18:00',
            'recorded_at' => now()->toISOString(),
            'is_fallback' => true,
        ];
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $e): void
    {
        // Log specific exceptions with context
        if ($e instanceof \App\Exceptions\WeatherServiceException) {
            \Log::error('Weather Service Exception', [
                'message' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);
        } elseif ($e instanceof \App\Exceptions\MarketplaceException) {
            \Log::error('Marketplace Exception', [
                'message' => $e->getMessage(),
                'order_id' => $e->getOrderId(),
            ]);
        } elseif ($e instanceof \App\Exceptions\InventoryException) {
            \Log::error('Inventory Exception', [
                'message' => $e->getMessage(),
                'item_id' => $e->getItemId(),
            ]);
        } else {
            parent::report($e);
        }
    }
}
