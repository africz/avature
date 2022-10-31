<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;


/**
 * BaseController
 */
class BaseController extends AbstractController {
    protected LoggerInterface $log;
    
    /**
     * __construct
     *
     * @param  mixed $logger
     * @return void
     */
    function __construct(LoggerInterface $logger)
    {
        $this->log = $logger;
    }
    
    /**
     * getFunc
     *
     * @param  mixed $function
     * @param  mixed $line
     * @return void
     */
    function getFunc($function,$line)
    {
        $path_parts = pathinfo(__FILE__);
        return $function."(),".$path_parts['filename'].':'.$line;
    }


}
