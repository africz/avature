<?php

namespace App\Controller;

use App\Entity\Position;
use App\Entity\Skills;
use App\Repository\PositionRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[ Route( '/position' ) ]

class SearchController extends AbstractController {

    private $client;

    public function __construct( HttpClientInterface $client ) {
        $this->client = $client;
    }

    public function fetchExternalJobSource( $name ): array {
        $response = $this->client->request(
            'GET',
            'http://localhost:8081/jobs?name='.$name
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()[ 'content-type' ][ 0 ];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = [ 'id' => 521583, 'name' => 'symfony-docs', ... ]

        return $content;
    }

    #[ Route( '/search', name:'app_position_search', methods:'POST' ) ]

    function search ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );
            //http://localhost:8081/jobs?name = Java
            $externalContent = array();
            for ( $i = 0; $i<count( $parameters[ 'name' ]); $i++ ) {
                $result=$this->fetchExternalJobSource( $parameters[ 'name' ][$i] );
                $externalContent=array_merge($result,$externalContent);


            }

            $retVal = '';
            $response = new JsonResponse( $retVal, 200 );
            //created
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }

}
