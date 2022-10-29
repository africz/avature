<?php

namespace App\Test\Controller;

use App\Entity\Position;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase {
    protected KernelBrowser $client;
    protected PositionRepository $repository;
    protected $path = '/position/';
    protected int $updateId = 0;
    protected int $invalidId = 99999999999;

    protected function setUp(): void {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get( 'doctrine' )->getRepository( Position::class );
        $position = $this->repository->findWithSmallestId();
        $this->updateId = $position[ 0 ]->getId();
    }

    function call ( $requestData, $method, $path ): string {
        $requestJson = json_encode( $requestData, JSON_THROW_ON_ERROR );
        $this->client->request( $method, sprintf( '%s'.$path, $this->path ), [
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'body' => $requestJson,
        ] );
        return $this->client->getResponse()->getContent();
    }

    //to surpress warns
    function testDummy()
    {
        self::assertSame( 1, 1 );
    }
}
