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
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[ Route( '/' ) ]

class DefaultController extends JobController {

    #[ Route( '/', name:'app_index', methods:['POST','GET','PUT','PATCH','DELETE'] ) ]

    function index ( Request $request ): JsonResponse {
        $response = null;
        try {
            $parameters = json_decode( $request->get( 'body' ), true );
            $retVal="Please read README file how to use this service properly!";
            $response = new JsonResponse( $retVal, 200 );
            //created
            return $response;
        } catch ( Exception $e ) {
            $response = new JsonResponse( [ 'error' => $e->getMessage() ], 400 );
            return $response;
        }

    }

}
