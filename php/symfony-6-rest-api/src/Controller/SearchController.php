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

    private $externalContent = array();
    private $internalContent = array();

    public function __construct( HttpClientInterface $client ) {
        $this->client = $client;
    }

    #[ Route( '/search', name:'app_position_search', methods:'POST' ) ]

    function search ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );

            for ( $i = 0; $i<count( $parameters[ 'name' ] );
            $i++ ) {
                $result = $this->fetchExternalJobSource( $parameters[ 'name' ][ $i ] );
                $this->externalContent = array_merge( $result, $this->externalContent );
            }

            for ( $i = 0; $i<count( $parameters[ 'name' ] );
            $i++ ) {
                $result = $this->fetchPositions( $parameters[ 'name' ][ $i ], $positionRepository );
                $this->internalContent = array_merge( $result, $this->internalContent );
            }

            $retVal = $this->merge_search();
            $response = new JsonResponse( $retVal, 200 );
            //created
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }

    public function fetchExternalJobSource( $name ): array {
        $response = $this->client->request(
            'GET',
            'http://localhost:8081/jobs?name='.$name
        );

        $statusCode = $response->getStatusCode();
        if ( $statusCode !== 200 ) {
            throw new Exception( EXTERNAL_SOURCE_FAIL );
        }
        $contentType = $response->getHeaders()[ 'content-type' ][ 0 ];
        $content = $response->getContent();
        $content = $response->toArray();
        return $content;
    }

    public function fetchPositions( $name, $positionRepository ) {
        $result = $positionRepository->findByName( $name );
        return $result;
    }

    public function merge_search() {
        $finalOutput = array();
        for ( $i = 0; $i<count( $this->internalContent );
        $i++ ) {
            $finalOutput[$i]=[ 'name'=>$this->internalContent[ $i ]->getName(), 
                                   'salary'=>$this->internalContent[ $i ]->getSalary(),
                                   'country'=>$this->internalContent[ $i ]->getCountry(),
                                   'skills'=>null
                                 ];

                                 $skills = $this->internalContent[ $i ]->getSkills();
                                     for ( $j = 0; $j<count( $skills );$j++ ) {
                                        $finalOutput[$i]['skills'][$j]= $skills[$j]->getName();
                                     }


        }

         return $finalOutput;
    }

}
