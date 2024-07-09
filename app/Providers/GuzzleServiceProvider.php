<?php

namespace App\Providers;

use App\Services\GuzzleMiddleware;
use App\Traits\FilterGuzzleStackLogs;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleServiceProvider extends ServiceProvider
{
    use FilterGuzzleStackLogs;
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            $stack = $this->getStack('gateway');
            return new Client([
                'handler' => $stack,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        });
    }

    private function getStack($channelName)
    {
        $sensitiveKeys = array('username', 'password', 'grant_type', 'refresh_token', 'api_key', 'authorization', 'Authorization', 'x-api-key', 'x-api-secret', 'x-api-version');
        $gatewayStack = HandlerStack::create();
        $gatewayStack->push(
            Middleware::mapRequest(function (RequestInterface $request) use ($channelName, $sensitiveKeys) {
                $recordings = [
                    'body' => $this->filterSensitiveData($request->getBody(), $sensitiveKeys),
                    'method' => $request->getMethod(),
                    'uri' => $request->getUri(),
                ];
                $recordings['headers'] = $this->filterSensitiveHeaders($request->getHeaders(), $sensitiveKeys);
                // Log the request details
                Log::channel($channelName)->info('Gateway request: ', $recordings);
                return $request;
            })
        );
        $gatewayStack->push(
            Middleware::mapResponse(function (ResponseInterface $response) use ($channelName) {
                // Log the response details
                Log::channel($channelName)->info('Service response: ' . $response->getStatusCode(), [
                    'headers' => $response->getHeaders(),
                    'body' => $response->getBody()
                ]);
                return $response;
            })
        );
        return $gatewayStack;
    }
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
