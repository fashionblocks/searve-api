<?php
namespace App\Gateway;

use GuzzleHttp\Client;

class ProjectGateway
{
    private $client;
    private $address;

    public function __construct(string $uri, string $address)
    {
        $this->client = new Client([
            'base_uri' => $uri,
            'timeout' => 30
        ]);
        $this->address = $address;
    }

    public function findMaxId() {

        $response = $this->client->request('post', '/project/max-id', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'address' => $this->address
            ])
        ]);

        return json_decode($response->getBody()->getContents(), 1)['projectId'];
    }

    public function findProjectInfo(int $projectId) {
        $response = $this->client->request('post', '/project/info', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'address' => $this->address,
                'projectId' => $projectId
            ])
        ]);

        return json_decode($response->getBody()->getContents(), 1);
    }

    public function findScript(int $projectId) {
        $response = $this->client->request('post', '/project/script', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'address' => $this->address,
                'projectId' => $projectId
            ])
        ]);

        return json_decode($response->getBody()->getContents(), 1)['script'];
    }


    public function findAllTokens(int $projectId) {
        $response = $this->client->request('post', '/token/all', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'address' => $this->address,
                'projectId' => $projectId
            ])
        ]);
        return json_decode($response->getBody()->getContents(), 1)['ids'];

    }

    public function findTokenHash(string $tokenId) {

        $response = $this->client->request('post', '/token/hash', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'address' => $this->address,
                'tokenId' => $tokenId
            ])
        ]);
        return json_decode($response->getBody()->getContents(), 1)['hash'];
    }
}
