<?php

namespace App\Test\Controller;

use App\Entity\Position;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PositionControllerTest extends WebTestCase {
    private KernelBrowser $client;
    private PositionRepository $repository;
    private $path = '/position/';

    protected function setUp(): void {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get( 'doctrine' )->getRepository( Position::class );

        //   foreach ( $this->repository->findAll() as $object ) {
        //       $this->repository->remove( $object, true );
        //   }
    }


    function call ( $requestData,$method,$path ): string {
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

    public function testNewOK(): void {
        $found = true;
        $baseName = 'PHP';
        $name = $baseName;
        for ( $i = 1; $found === true; $i++ ) {
            $position = $this->repository->findOneBy( [ 'name' => $name ] );
            if ( empty( $position ) ) {
                $found = false;
            } else {
                $name = $baseName. $i;
            }
        }

        $requestData = [ 'name' => $name, 'salary' => '5000', 'country' => 'Hungary', 'skills'=>[ 'php', 'c++', 'node' ] ];
        $response = json_decode( $this->call($requestData,"POST","new"), true );

        self::assertResponseStatusCodeSame( 201 );
        self::assertSame( $requestData[ 'name' ], $response[ 'name' ] );
    }

    public function testNewNameEmpty(): void {
        $requestData = [ 'name' => '', 'salary' => '5000', 'country' => 'Hungary' ];
        $response = $this->call($requestData,"POST","new");
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.NAME_IS_EMPTY.'"}', $response );
    }

    public function testNewSalaryEmpty(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '', 'country' => 'Hungary' ];
        $response = $this->call($requestData,"POST","new");
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.SALARY_IS_EMPTY.'"}', $response );
    }

    public function testNewCountryEmpty(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '5000', 'country' => '' ];
        $response = $this->call($requestData,"POST","new");
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.COUNTRY_IS_EMPTY.'"}', $response );
    }

    public function testNewNoSkills(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '5000', 'country' => 'Hungary' ];
        $response = $this->call($requestData,"POST","new");
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"Undefined array key \u0022skills\u0022"}', $response );
    }

    public function testNewSkillsEmpty(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '5000', 'country' => 'Hungary', 'skills'=>[] ];
        $response = $this->call($requestData,"POST","new");
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.SKILLS_ARE_EMPTY.'"}', $response );
    }

    public function testPut(): void {
        $requestData = [ 'id'=>89, 'name'=>'xyz', 'salary' => '5000', 'country' => 'Hungary', 'skills'=>[ 'php1', 'javascript', 'node' ] ];
        $response = $this->call( $requestData,"PUT","update" );
        self::assertResponseStatusCodeSame( 200 );
        self::assertSame( '{"id":'.$requestData[ 'id' ].',"name":"'.$requestData[ 'name' ].'"}', $response );
    }

    public function testPutNotFound(): void {
        $requestData = [ 'id'=>7, 'name'=>'xyz', 'salary' => '5000', 'country' => 'Hungary', 'skills'=>[ 'php', 'c++', 'node' ] ];
        $response = $this->call( $requestData,"PUT","update" );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"Id:'.$requestData[ 'id' ].' not found!"}', $response );
    }

    //  public function testPatch(): void {
    //      $requestData = [ 'id'=>89, 'name'=>'patchooo'];
    //      $response = $this->call( $requestData,"PATCH","update" );
    //      self::assertResponseStatusCodeSame( 200 );
    //      self::assertSame( '{"id":'.$requestData[ 'id' ].',"name":"'.$requestData[ 'name' ].'"}', $response );
    //  }


}
