<?php
namespace App\Gateway;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

class SnapGateway
{

    private $client;
    private $generateUri;

    public function __construct(EntityManagerInterface $em, string $uri, string $generateUri)
    {
        $this->em = $em;
        $this->client = new Client([
            'timeout' => 60,
            'base_uri' => $uri
        ]);
        $this->generateUri = $generateUri;
    }

    public function snap(string $url, string $filename)
    {
        $response = $this->client->request('post', '/snap', [
            'form_params' => [
                'url' => $this->generateUri.'/'.$url,
                'name' => $filename
            ]
        ]);

        return $response->getStatusCode() === 200;
    }

}
