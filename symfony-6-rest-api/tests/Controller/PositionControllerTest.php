<?php

namespace App\Test\Controller;

use App\Entity\Position;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Test\Controller\BaseControllerTest;
use App\ErrorMessages;

/**
 * PositionControllerTest
 */
class PositionControllerTest extends BaseControllerTest {
    
    /**
     * testNewOK
     * Test how to create a new record successfully without any errors.
     *
     * @return void
     */
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
        $response = json_decode( $this->call( $requestData, 'POST', 'new' ), true );

        self::assertResponseStatusCodeSame( 201 );
        self::assertSame( $requestData[ 'name' ], $response[ 'name' ] );
    }
    
    /**
     * testNewNameEmpty
     * Test how to handle error if name parameter is empty.
     * 
     * @return void
     */
    public function testNewNameEmpty(): void {
        $requestData = [ 'name' => '', 'salary' => '5000', 'country' => 'Hungary' ];
        $response = $this->call( $requestData, 'POST', 'new' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.ErrorMessages::NAME_IS_EMPTY.'"}', $response );
    }
    
    /**
     * testNewSalaryEmpty
     * Test new record creation with empty salary parameter.
     *
     * @return void
     */
    public function testNewSalaryEmpty(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '', 'country' => 'Hungary' ];
        $response = $this->call( $requestData, 'POST', 'new' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.ErrorMessages::SALARY_IS_EMPTY.'"}', $response );
    }
    
    /**
     * testNewCountryEmpty
     * Test new record creation with empty country parameter.
     *
     * @return void
     */
    public function testNewCountryEmpty(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '5000', 'country' => '' ];
        $response = $this->call( $requestData, 'POST', 'new' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.ErrorMessages::COUNTRY_IS_EMPTY.'"}', $response );
    }

    public function testNewNoSkills(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '5000', 'country' => 'Hungary' ];
        $response = $this->call( $requestData, 'POST', 'new' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"Undefined array key \u0022skills\u0022"}', $response );
    }

    public function testNewSkillsEmpty(): void {
        $requestData = [ 'name' => 'PHP', 'salary' => '5000', 'country' => 'Hungary', 'skills'=>[] ];
        $response = $this->call( $requestData, 'POST', 'new' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.ErrorMessages::SKILLS_ARE_EMPTY.'"}', $response );
    }

    public function testPut(): void {
        $requestData = [ 'id'=>$this->updateId, 'name'=>'xyz', 'salary' => '5000', 'country' => 'Hungary', 'skills'=>[ 'php1', 'javascript', 'node' ] ];
        $response = $this->call( $requestData, 'PUT', 'update' );
        self::assertResponseStatusCodeSame( 200 );
        self::assertSame( '{"id":'.$requestData[ 'id' ].',"name":"'.$requestData[ 'name' ].'"}', $response );
    }

    public function testPutNotFound(): void {
        $requestData = [ 'id'=>$this->invalidId, 'name'=>'xyz', 'salary' => '5000', 'country' => 'Hungary', 'skills'=>[ 'php', 'c++', 'node' ] ];
        $response = $this->call( $requestData, 'PUT', 'update' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"Id:'.$requestData[ 'id' ].' not found!"}', $response );
    }

    public function testPatch(): void {
        $requestData = [ 'id'=>$this->updateId, 'name'=>'patchooo' ];
        $response = $this->call( $requestData, 'PATCH', 'update' );
        self::assertResponseStatusCodeSame( 200 );
        self::assertSame( '{"id":'.$requestData[ 'id' ].',"name":"'.$requestData[ 'name' ].'"}', $response );
    }

    public function testPatchNoFields(): void {
        $requestData = [ 'id'=>$this->updateId ];
        $response = $this->call( $requestData, 'PATCH', 'update' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.ErrorMessages::PARAMETERS_ARE_EMPTY.'"}', $response );
    }

    public function testDelete(): void {
        $requestData = [ 'id'=>$this->updateId ];
        $response = $this->call( $requestData, 'DELETE', 'delete' );
        self::assertResponseStatusCodeSame( 200 );
        self::assertSame( '{"id":'.$requestData[ 'id' ].'}', $response );
    }

    public function testDeleteError(): void {
        $requestData = [ 'id'=>$this->invalidId ];
        $response = $this->call( $requestData, 'DELETE', 'delete' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.ErrorMessages::POSITION_NOT_FOUND.'"}', $response );
    }


}
