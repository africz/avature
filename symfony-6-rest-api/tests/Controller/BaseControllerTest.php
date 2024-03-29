<?php

namespace App\Test\Controller;

use App\Entity\Position;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Psr\Log\LoggerInterface;
use App\ErrorMessages;

/**
 * BaseControllerTest
 */
class BaseControllerTest extends WebTestCase {
    protected KernelBrowser $client;
    protected PositionRepository $repository;
    protected $path = '/position/';
    protected int $updateId = 0;
    protected int $invalidId = 99999999999;
    protected LoggerInterface $log;

    protected function setUp(): void {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get( 'doctrine' )->getRepository( Position::class );
        $this->log = static::getContainer()->get( 'logger' );
        $position = $this->repository->findWithSmallestId();
        $this->updateId = $position[ 0 ]->getId();
    }
    
    /**
     * getFunc
     * Create debug info for log entries
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
    
    /**
     * call
     *
     * @param  mixed $requestData
     * @param  mixed $method
     * @param  mixed $path
     * @return string
     */
    function call ( $requestData, $method, $path ): string {
        $this->log->debug($this->getFunc(__FUNCTION__,__LINE__),['request data'=>$requestData,'method'=>$method,'path'=>$path]);
        $requestJson = json_encode( $requestData, JSON_THROW_ON_ERROR );
        $this->client->request( $method, sprintf( '%s'.$path, $this->path ), [
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'body' => $requestJson,
        ] );
        return $this->client->getResponse()->getContent();
    }

    //to surpress warns
    function testDummy()
    {
        self::assertSame( 1, 1 );
    }
}
