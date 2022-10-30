<?php

namespace App\Test\Controller;

use App\Entity\Position;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Test\Controller\JobControllerTest;

/**
 * SearchControllerTest
 */
class SearchControllerTest extends JobControllerTest {

    public function testSearchBySingleName(): void {
        $requestData = [ 'name'=>[ 'java'] ];
        $response = $this->call( $requestData, 'POST', 'search' );
        self::assertResponseStatusCodeSame( 200 );
    }

    public function testSearchByMultiplyName(): void {
        $requestData = [ 'name'=>[ 'php', 'java', 'c++' ] ];
        $response = $this->call( $requestData, 'POST', 'search' );
        self::assertResponseStatusCodeSame( 200 );
    }

    public function testSearchByEmptyNameError(): void {
        $requestData = [ 'name'=>[''] ];
        $response = $this->call( $requestData, 'POST', 'search' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"'.NAME_IS_EMPTY.'"}', $response );
    }

    public function testSearchByNoName(): void {
        $requestData = [];
        $response = $this->call( $requestData, 'POST', 'search' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"Undefined array key \u0022name\u0022"}', $response );
    }
    public function testSearchByNameNoParam(): void {
        $requestData = null;
        $response = $this->call( $requestData, 'POST', 'search' );
        self::assertResponseStatusCodeSame( 400 );
        self::assertSame( '{"error":"Trying to access array offset on value of type null"}', $response );
    }

}
