<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;


class JobController extends AbstractController {
    protected LoggerInterface $log;

    function __construct(LoggerInterface $logger)
    {
        $this->log = $logger;
    }

    function getFunc($function,$line)
    {
        $path_parts = pathinfo(__FILE__);
        return $function."(),".$path_parts['filename'].':'.$line;
    }


}
