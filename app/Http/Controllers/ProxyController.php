<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProxyController extends Controller
{
    protected $profileServiceUrl;

    public function __construct(private readonly Client $client)
    {
        $this->profileServiceUrl = config('services.micro-services.profile'); // URL of the User Profile Service
    }

    protected function prepareRequest(Request $request, $url, $path = null): ResponseInterface
    {
        $user = $request->user();
        // Prepare the data to be sent to the external API
        $data = [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ];

        $originalData = $request->json()->all();
        $data = array_merge($originalData, $data);

        return $this->client->request($request->method(), $url, [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'json' => $data,
        ]);
    }

    public function handleProfile(Request $request, $path = null)
    {
        $url = $this->profileServiceUrl.'/profile/'.$path;

        return $this->prepareRequest($request, $url, $path);
    }

    public function handleAdminProfile(Request $request)
    {
        $url = $this->profileServiceUrl.'/profile/admin';

        return $this->prepareRequest($request, $url);
    }

    public function handleDynamic(Request $request, $service, $path = null)
    {
        $url = $this->getServiceUrl($service).'/'.$service.'/'.$path;

        return $this->prepareRequest($request, $url, $path);
    }

    private function getServiceUrl($service)
    {
        // Define your service URLs here
        $services = [
            'profile' => $this->profileServiceUrl,
            // Add more services as needed
        ];

        if (array_key_exists($service, $services)) {
            return $services[$service];
        }

        throw new NotFoundHttpException('Service not found');
    }
}
