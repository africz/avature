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

#[ Route( '/position' ) ]

class PositionController extends AbstractController {

    #[ Route( '/new', name:'app_position_new', methods:'POST' ) ]

    function new ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );

            $position = new Position();
            $newPosition = $this->insert( $parameters, $position, $entityManager );
            $retVal = [ 'id'=>$newPosition->getId(), 'name'=>$newPosition->getName() ];
            $response = new JsonResponse( $retVal, 201 );
            //created
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }
    //new

    #[ Route( '/update', name:'app_position_update', methods:[ 'PUT', 'PATCH' ] ) ]

    function update ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );
            $position = $positionRepository->findOneBy( [ 'id'=>$parameters[ 'id' ] ] );
            if ( empty( $position ) ) {
                throw new Exception( 'Id:'.$parameters[ 'id' ].' not found!' );
            }
            if ( $request->isMethod( 'PUT' ) ) {
                $newPosition = $this->put( $parameters, $position, $entityManager );
            }
            if ( $request->isMethod( 'PATCH' ) ) {
                $newPosition = $this->patch( $parameters, $position, $entityManager );
            }
            //var_dump( $position );
            $retVal = [ 'id'=>$newPosition->getId(), 'name'=>$newPosition->getName() ];
            $response = new JsonResponse( $retVal, 200 );
            //created
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }

    #[ Route( '/delete', name:'app_position_delete', methods:'DELETE' ) ]

    function delete ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );
            $position = $positionRepository->findOneBy( [ 'id'=>$parameters[ 'id' ] ] );
            if ( $position ) {
                $removeId=$position->getId();
                $positionRepository->remove( $position, true );
            }else
            {
                throw new Exception(POSITION_NOT_FOUND);
            }

            $retVal = [ 'id'=>$removeId ];
            $response = new JsonResponse( $retVal, 200 );
            //created
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }//delete
    
    #[ Route( '/search', name:'app_position_search', methods:'POST' ) ]
    function search ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );

            $retVal = [ 'id'=>$removeId ];
            $response = new JsonResponse( $retVal, 200 );
            //created
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }//delete

    function verifyPosition( $parameters ) {
        //var_dump( $parameters );
        if ( empty( $parameters[ 'name' ] ) ) {
            throw new Exception( NAME_IS_EMPTY );
        }
        if ( empty( $parameters[ 'salary' ] ) ) {
            throw new Exception( SALARY_IS_EMPTY );
        }
        if ( empty( $parameters[ 'country' ] ) ) {
            throw new Exception( COUNTRY_IS_EMPTY );
        }
        if ( !count( $parameters[ 'skills' ] ) ) {
            throw new Exception( SKILLS_ARE_EMPTY );
        }

    }

    function insert( $parameters, $position, $entityManager ) {
        $this->verifyPosition( $parameters );
        $position->setName( $parameters[ 'name' ] );
        $position->setSalary( $parameters[ 'salary' ] );
        $position->setCountry( $parameters[ 'country' ] );
        foreach ( $parameters[ 'skills' ] as $skill_name ) {
            $skills = new Skills();
            $skills->setName( $skill_name );
            $position->addSkill( $skills );
            $entityManager->persist( $skills );

        }
        $entityManager->persist( $position );
        $entityManager->flush();
        return $position;
    }

    function put( $parameters, $position, $entityManager ) {
        $this->verifyPosition( $parameters );
        $position->setName( $parameters[ 'name' ] );
        $position->setSalary( $parameters[ 'salary' ] );
        $position->setCountry( $parameters[ 'country' ] );
        $oldSkills = $position->getSkills();
        foreach ( $parameters[ 'skills' ] as $skill_name ) {
            $found = false;
            for ( $i = 0; $i<count( $oldSkills );
            $i++ ) {
                if ( $skill_name === $oldSkills[ $i ]->getName() ) {
                    $found = true;
                    break;
                }
            }
            if ( !$found ) {
                $skills = new Skills();
                $skills->setName( $skill_name );
                $position->addSkill( $skills );
                $entityManager->persist( $skills );

            }
        }
        $entityManager->persist( $position );
        $entityManager->flush();
        return $position;
    }

    function patch( $parameters, $position, $entityManager ) {
        //var_dump( $parameters );
        $count = 0;
        if ( !empty( $parameters[ 'name' ] ) ) {
            $position->setName( $parameters[ 'name' ] );
            $count++;
        }
        if ( !empty( $parameters[ 'salary' ] ) ) {
            $position->setName( $parameters[ 'salary' ] );
            $count++;
        }
        if ( !empty( $parameters[ 'country' ] ) ) {
            $position->setName( $parameters[ 'country' ] );
            $count++;
        }
        if ( !empty( $parameters[ 'skills' ] )  && count( $parameters[ 'skills' ] ) ) {
            $oldSkills = $position->getSkills();
            foreach ( $parameters[ 'skills' ] as $skill_name ) {
                $found = false;
                for ( $i = 0; $i<count( $oldSkills );
                $i++ ) {
                    if ( $skill_name === $oldSkills[ $i ]->getName() ) {
                        $found = true;
                        break;
                    }
                }
                if ( !$found ) {
                    $count++;
                    $skills = new Skills();
                    $skills->setName( $skill_name );
                    $position->addSkill( $skills );
                    $entityManager->persist( $skills );

                }
            }
        }
        if ( !$count ) {
            throw new Exception( PARAMETERS_ARE_EMPTY );
        }
        $entityManager->persist( $position );
        $entityManager->flush();
        return $position;
    }
}
