<?php

namespace App\Controller;

use App\Entity\Position;
use App\Entity\Skills;
use App\ErrorMessages;
use App\Repository\PositionRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[ Route( '/position' ) ]

/**
* SearchController
*/

class SearchController extends BaseController {

    private $client;

    private $externalContent = array();
    private $internalContent = array();

    /**
    * search
    * Search by name multiply name allowed in an array
    *
    * Request Data
    * ------------
    * {
        *    'name':
        *    [
            *        'php',
            *        'java',
            *        'c++'
            * ]
            *  }

            *
            * Response Success
            * ----------------
            * {
                *    '0': {
                    *        'name':'Jr C++ developer-10',
                    *        'salary':90000,
                    *        'country':'Cyprus',
                    *        'skills':[ 'php', 'linux' ]
                    *        }
                    ,
                    *    '1': {
                        *       'name':'Jr C++ developer-17',
                        *       'salary':40000,
                        *       'country':'Chile',
                        *       'skills':[ 'mac', 'javascript', 'oracle', 'mac', 'node', 'php' ]}
                        ,
                        *       ....
                        *
                        * Response Error
                        * --------------
                        * {
                            *    'error':'Name is empty!'
                            * }
                            *
                            * @param  mixed $request
                            * @param  mixed $positionRepository
                            * @param  mixed $doctrine
                            * @param  mixed $client
                            * @return JsonResponse
                            */
                            #[ Route( '/search', name:'app_position_search', methods:'POST' ) ]

                            function search ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine, HttpClientInterface $client ): JsonResponse {
                                $response = null;
                                try {
                                    $entityManager = $doctrine->getManager();
                                    $parameters = json_decode( $request->get( 'body' ), true );
                                    $this->client = $client;
                                    $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );

                                    if ( !count( $parameters[ 'name' ] ) ) {
                                        throw new Exception( ErrorMessages::NAME_IS_EMPTY );
                                    }

                                    for ( $i = 0; $i<count( $parameters[ 'name' ] );
                                    $i++ ) {
                                        if ( trim( $parameters[ 'name' ][ $i ] === '' ) ) {
                                            throw new Exception( ErrorMessages::NAME_IS_EMPTY );
                                        }

                                        $result = $this->fetchExternalJobSource( $parameters[ 'name' ][ $i ] );
                                        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'external'=>$result ] );
                                        $this->externalContent = array_merge( $result, $this->externalContent );
                                    }

                                    for ( $i = 0; $i<count( $parameters[ 'name' ] );
                                    $i++ ) {
                                        $result = $this->fetchPositions( $parameters[ 'name' ][ $i ], $positionRepository );
                                        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'internal'=>$result ] );
                                        $this->internalContent = array_merge( $result, $this->internalContent );
                                    }

                                    $retVal = $this->merge_search( $parameters );
                                    $response = new JsonResponse( $retVal, 200 );
                                    $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'response'=>$response ] );
                                    return $response;
                                } catch ( Exception $e ) {
                                    $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
                                    $this->log->error( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'message'=>$e->getMessage() ] );
                                    return $response;
                                }
                            }

                            /**
                            * fetchExternalJobSource
                            * Retrive jobs from external resource
                            *
                            * @param  mixed $name
                            * @return array
                            */

                            private function fetchExternalJobSource( $name ): array {
                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'name'=>$name ] );
                                $eMethod = $this->getParameter( 'app.jobsource.method' );
                                $eUrl = $this->getParameter( 'app.jobsource.url' );
                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'method'=>$eMethod,'url'=>$eUrl ] );                                

                                $response = $this->client->request(
                                    $eMethod,
                                    $eUrl.$name
                                );

                                $statusCode = $response->getStatusCode();
                                if ( $statusCode !== 200 ) {
                                    throw new Exception( ErrorMessages::EXTERNAL_SOURCE_FAIL );

                                }
                                $contentType = $response->getHeaders()[ 'content-type' ][ 0 ];
                                $content = $response->getContent();
                                $content = $response->toArray();
                                return $content;
                            }

                            /**
                            * fetchPositions
                            * Retrive positions from db
                            *
                            * @param  mixed $name
                            * @param  mixed $positionRepository
                            * @return void
                            */

                            private function fetchPositions( $name, $positionRepository ) {
                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'name'=>$name ] );
                                $result = $positionRepository->findByName( ' '.$name.' ' );
                                return $result;
                            }
                            //fetchPositions

                            /**
                            * filter_search
                            * remove those records that not match with given country or salary records
                            *
                            * @param  mixed $parameters
                            * @param  mixed $finalOutput
                            * @return void
                            */

                            private function filter_search( $parameters, $finalOutput ) {
                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters, 'finalOutput'=>$finalOutput ] );
                                $i = 0;
                                $toRemove = array();
                                while ( $i<count( $finalOutput ) ) {
                                    $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'finalOutput[i]'=>$finalOutput[ $i ] ] );
                                    if ( !empty( $parameters[ 'country' ] ) ) {
                                        $j = 0;
                                        while( $j<count( $parameters[ 'country' ] ) ) {
                                            $found = false;
                                            if ( ( strtolower( $finalOutput[ $i ][ 'country' ] ) ) === ( strtolower( $parameters[ 'country' ][ $j ] ) ) ) {
                                                $found = true;
                                                break;
                                            }
                                            $j++;
                                        }
                                        if ( !$found ) {
                                            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'toRemove(country):finalOutput[i]'=>$finalOutput[ $i ] ] );
                                            array_push( $toRemove, $i );
                                        }

                                    }
                                    if ( !empty( $parameters[ 'salary' ] ) ) {
                                        if ( $finalOutput[ $i ][ 'salary' ]  <= $parameters[ 'salary' ] ) {
                                            if ( !in_array( $i, $toRemove ) ) {
                                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'toRemove(salary):finalOutput[i]'=>$finalOutput[ $i ] ] );
                                                array_push( $toRemove, $i );
                                            }
                                        }
                                    }

                                    $i++;
                                }
                                $i = 0;
                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'toRemove(all)'=>$toRemove ] );
                                while( $i<count( $toRemove ) ) {
                                    unset( $finalOutput[ $toRemove[ $i ] ] );
                                    $i++;
                                }
                                //print_r( $toRemove );
                                //print_r( $finalOutput );
                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'return:finalOutput'=>$finalOutput ] );
                                return $finalOutput;

                            }
                            //filter_search

                            /**
                            * merge_search
                            * Merge and transform external and internal source to one response
                            *
                            * @return void
                            */

                            private function merge_search( $parameters ) {
                                $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );
                                $finalOutput = array();
                                for ( $i = 0; $i<count( $this->internalContent );
                                $i++ ) {
                                    $finalOutput[ $i ] = [ 'name'=>$this->internalContent[ $i ]->getName(),
                                    'salary'=>$this->internalContent[ $i ]->getSalary(),
                                    'country'=>$this->internalContent[ $i ]->getCountry(),
                                    'skills'=>null
                                ];

                                $skills = $this->internalContent[ $i ]->getSkills();
                                for ( $j = 0; $j<count( $skills );
                                $j++ ) {
                                    $finalOutput[ $i ][ 'skills' ][ $j ] = $skills[ $j ]->getName();
                                }
                            }

                            $j = $i;
                            for ( $i = 0; $i<count( $this->externalContent );
                            $i++ ) {
                                $eName = $this->externalContent[ $i ][ 0 ];
                                $eCountry = $this->externalContent[ $i ][ 1 ];
                                $eSalary = $this->externalContent[ $i ][ 2 ];
                                $eSkills = $this->externalContent[ $i ][ 3 ];

                                $finalOutput[ $j ] = [ 'name'=>$eName, 'salary'=>$eSalary, 'country'=>$eCountry, 'skill'=>$eSkills ];
                                $j++;
                            }
                            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'finalOutput'=>$finalOutput ] );
                            return $this->filter_search( $parameters, $finalOutput );
                        }

                    }
