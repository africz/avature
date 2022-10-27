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
            //var_dump( $parameters );
            if ( empty( $parameters[ 'name' ] ) ) {
                throw new Exception( 'Name is empty!' );
            }
            if ( empty( $parameters[ 'salary' ] ) ) {
                throw new Exception( 'Salary is empty!' );
            }
            if ( empty( $parameters[ 'country' ] ) ) {
                throw new Exception( 'Country is empty!' );
            }
            if ( !count( $parameters[ 'skills' ] ) ) {
                throw new Exception( 'Skills are empty!' );
            }

            $position = new Position();

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
            //$positionRepository->save( $position, true );
            //var_dump( $position );
            $retVal = [ 'id'=>$position->getId(), 'name'=>$position->getName() ];
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
                throw new Exception( 'Id:'.$parameters['id'].' not found!' );
            }
            if ( $request->isMethod( 'PUT' ) ) {
                $uPosition = $this->put( $parameters, $position, $entityManager );
            }
            if ( $request->isMethod( 'PATCH' ) ) {
                $this->patch( $parameters );

            }
            //var_dump( $position );
            $retVal = [ 'id'=>$uPosition->getId(), 'name'=>$uPosition->getName() ];
            $response = new JsonResponse( $retVal, 200 );
            //created
            return $response;
        } catch ( Exception $e ) {
            //$retVal = $this->exceptionError( $request->query, $e->getFile(), $e->getMessage(), $e->getLine(), $e->getTrace() );
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }
    }

    function put( $parameters, $position, $entityManager ) {
        if ( empty( $parameters[ 'name' ] ) ) {
            throw new Exception( 'Name is empty!' );
        }
        if ( empty( $parameters[ 'salary' ] ) ) {
            throw new Exception( 'Salary is empty!' );
        }
        if ( empty( $parameters[ 'country' ] ) ) {
            throw new Exception( 'Country is empty!' );
        }
        if ( !count( $parameters[ 'skills' ] ) ) {
            throw new Exception( 'Skills are empty!' );
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

    }

}
