<?php

namespace App\Test\Controller;

use App\Entity\Position;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchControllerTest extends WebTestCase {
    private KernelBrowser $client;
    private PositionRepository $repository;
    private $path = '/position/';
    private int $updateId = 0;
    private int $invalidId = 99999999999;

    protected function setUp(): void {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get( 'doctrine' )->getRepository( Position::class );
        $position = $this->repository->findWithSmallestId();
        $this->updateId = $position[ 0 ]->getId();
        $tmp = '';

        //   foreach ( $this->repository->findAll() as $object ) {
        //       $this->repository->remove( $object, true );
        //   }
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

    public function testSearchBySingleName(): void {
        $requestData = [ 'name'=>[ 'java'] ];
        $response = $this->call( $requestData, 'POST', 'search' );
        // print_r($response);
        self::assertResponseStatusCodeSame( 200 );
    }


    public function testSearchByMultiplyName(): void {
        $requestData = [ 'name'=>[ 'php', 'java', 'c++' ] ];
        $response = $this->call( $requestData, 'POST', 'search' );
        //print_r($response);
        self::assertResponseStatusCodeSame( 200 );
    }

}
