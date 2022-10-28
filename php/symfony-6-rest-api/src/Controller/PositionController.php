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
            //$retVal = $this->exceptionError( $request->query, $e->getFile(), $e->getMessage(), $e->getLine(), $e->getTrace() );
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
                $newPosition = $this->insert( $parameters, $position, $entityManager );
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
            //$retVal = $this->exceptionError( $request->query, $e->getFile(), $e->getMessage(), $e->getLine(), $e->getTrace() );
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }

    function insert( $parameters, $position, $entityManager ) {
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

        //var_dump( $parameters );
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

    function patch( $parameters ) {
        //var_dump( $parameters );
        if (!count( $parameters) ) {
            throw new Exception( PARAMETERS_ARE_EMPTY );
        }

        //var_dump( $parameters );
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

}
