<?php

namespace App\Services;

use GuzzleHttp\Client;

class AutzService
{
    protected $autzServiceUrl;

    public function __construct(private readonly Client $client)
    {
        $this->autzServiceUrl = config('services.micro-services.authorization'); // URL of the User Authentication Service
    }

    public function checkPermissions(array $data)
    {
        return $this->client->post("{$this->autzServiceUrl}/check-permissions", [
            'json' => $data,
        ]);
    }

    public function getRoles(array $data)
    {
        return $this->client->post("{$this->autzServiceUrl}/get-roles", [
            'json' => $data,
        ]);
    }
}
