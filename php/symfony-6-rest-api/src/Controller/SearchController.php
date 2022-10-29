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
    private $externalContent=array();

    public function __construct( HttpClientInterface $client ) {
        $this->client = $client;
    }

    public function fetchExternalJobSource( $name ): array {
        $response = $this->client->request(
            'GET',
            'http://localhost:8081/jobs?name='.$name
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode!==200)
        {
            throw new Exception(EXTERNAL_SOURCE_FAIL);
        }
        $contentType = $response->getHeaders()[ 'content-type' ][ 0 ];
        $content = $response->getContent();
        $content = $response->toArray();
        return $content;
    }

    #[ Route( '/search', name:'app_position_search', methods:'POST' ) ]

    function search ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );
            //http://localhost:8081/jobs?name = Java
            for ( $i = 0; $i<count( $parameters[ 'name' ]); $i++ ) {
                $result=$this->fetchExternalJobSource( $parameters[ 'name' ][$i] );
                $this->externalContent=array_merge($result,$this->externalContent);
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
