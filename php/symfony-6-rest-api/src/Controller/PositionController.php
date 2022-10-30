<?php

namespace App\Controller;

use App\Entity\Position;
use App\Entity\Skills;
use App\Repository\PositionRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
* PositionController handle new, update, delete requests
*/
#[ Route( '/position' ) ]

class PositionController extends BaseController {


   /**
    * new
    *
    * Request Data
    * ------------
    * {
    *   "name":"PHP",
    *   "salary":"5000",
    *   "country":"Hungary",
    *       "skills":
    *         [
    *          "php",
    *          "c++",
    *          "node"
    *         ]
    * }
    *
    * Response Success
    * ----------------
    * {
    *   "id":142,
    *   "name":"PHP"
    * }
    *
    * Response Error
    * --------------
    * {
    *    "error":"Country is empty!"
    * }
    * 
    *
    * @param  mixed $request
    * @param  mixed $positionRepository
    * @param  mixed $doctrine
    * @return JsonResponse
    */
    #[ Route( '/new', name:'app_position_new', methods:'POST' ) ]

    function new ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );
            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );

            $position = new Position();
            $newPosition = $this->insert( $parameters, $position, $entityManager );
            $retVal = [ 'id'=>$newPosition->getId(), 'name'=>$newPosition->getName() ];
            $response = new JsonResponse( $retVal, 201 );
            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'response'=>$response ] );
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            $this->log->error( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'message'=>$e->getMessage() ] );
            return $response;
        }
    }
    /*new*/

    /**
    * update 
    *
    * Handle full update (PUT) and partial update requests (PATCH)
    *
    * Request Data
    * ------------
    * {
    *   "name":"PHP",
    *   "salary":"5000",
    *   "country":"Hungary",
    *       "skills":
    *         [
    *          "php",
    *          "c++",
    *          "node"
    *         ]
    * }
    *
    * Response Success
    * ----------------
    * {
    *   "id":142,
    *   "name":"PHP"
    * }
    *
    * Response Error
    * --------------
    * {
    *    "error":"Country is empty!"
    * }
    * 
    *
    * @param  mixed $request
    * @param  mixed $positionRepository
    * @param  mixed $doctrine
    * @return JsonResponse
    */
    #[ Route( '/update', name:'app_position_update', methods:[ 'PUT', 'PATCH' ] ) ]

    function update ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );
            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );
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
            $retVal = [ 'id'=>$newPosition->getId(), 'name'=>$newPosition->getName() ];
            $response = new JsonResponse( $retVal, 200 );
            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'response'=>$response ] );
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            $this->log->error( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'message'=>$e->getMessage() ] );
            return $response;
        }
    }
    /*update*/

    /**
    * delete
    *
    * Request Data
    * ------------
    * {
    *    "id":141
    * }
    *
    * Response Success
    * ----------------
    * {
    *   "id":141,
    * }
    *
    * Response Error
    * --------------
    * {
    *    "error":"Position not found!"
    * }
    * 
    *
    * @param  mixed $request
    * @param  mixed $positionRepository
    * @param  mixed $doctrine
    * @return JsonResponse
    */
    #[ Route( '/delete', name:'app_position_delete', methods:'DELETE' ) ]

    function delete ( Request $request, PositionRepository $positionRepository, ManagerRegistry $doctrine ): JsonResponse {
        $response = null;
        try {
            $entityManager = $doctrine->getManager();
            $parameters = json_decode( $request->get( 'body' ), true );
            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );
            $position = $positionRepository->findOneBy( [ 'id'=>$parameters[ 'id' ] ] );
            if ( $position ) {
                $removeId = $position->getId();
                $positionRepository->remove( $position, true );
            } else {
                throw new Exception( POSITION_NOT_FOUND );
            }

            $retVal = [ 'id'=>$removeId ];
            $response = new JsonResponse( $retVal, 200 );
            $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'response'=>$response ] );
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            $this->log->error( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'message'=>$e->getMessage() ] );
            return $response;
        }
    }
    /*delete*/

    
    /**
    * verify position members
    * 
    * @param  mixed $parameters
    * @return void
    */
    function verifyPosition( $parameters ) {
        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );
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
    /*verifyPosition*/
    
    /**
     * insert
     *
     * @param  mixed $parameters
     * @param  mixed $position
     * @param  mixed $entityManager
     * @return void
     */
    function insert( $parameters, $position, $entityManager ) {
        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );
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
        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'position'=>$position ] );
        return $position;
    }
    /*insert*/
    
    /**
     * put
     *
     * @param  mixed $parameters
     * @param  mixed $position
     * @param  mixed $entityManager
     * @return void
     */
    function put( $parameters, $position, $entityManager ) {
        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );
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
        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'position'=>$position ] );
        return $position;
    }
    /*put*/
    
    /**
     * patch
     *
     * @param  mixed $parameters
     * @param  mixed $position
     * @param  mixed $entityManager
     * @return void
     */
    function patch( $parameters, $position, $entityManager ) {
        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'parameters'=>$parameters ] );
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
        $this->log->debug( $this->getFunc( __FUNCTION__, __LINE__ ), [ 'position'=>$position ] );
        return $position;
    }
}
/*patch*/
