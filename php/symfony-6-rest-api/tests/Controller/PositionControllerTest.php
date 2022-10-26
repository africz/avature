<?php

namespace App\Test\Controller;

use App\Entity\Position;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PositionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private PositionRepository $repository;
    private $path = '/position/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Position::class);

        // foreach ($this->repository->findAll() as $object) {
        //     $this->repository->remove($object, true);
        // }
    }

    function new ($requestData): string {
        $requestJson = json_encode($requestData, JSON_THROW_ON_ERROR);
        $this->client->request('POST', sprintf('%snew', $this->path), [
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'body' => $requestJson,
        ]);
        return $this->client->getResponse()->getContent();
    }

    public function testNewOK(): void
    {
        $requestData = ["name" => "PHP", "salary" => "5000", "country" => "Hungary"];
        $response = $this->new($requestData);
        self::assertResponseStatusCodeSame(200);
        self::assertSame('{"result":"OK"}', $response);
    }

    public function testNewNameError(): void
    {
        $requestData = ["name" => "", "salary" => "5000", "country" => "Hungary"];
        $response = $this->new($requestData);
        self::assertResponseStatusCodeSame(400);
        self::assertSame('{"result":"Name is empty!"}', $response);
    }

    public function testNewSalaryError(): void
    {
        $requestData = ["name" => "PHP", "salary" => "", "country" => "Hungary"];
        $response = $this->new($requestData);
        self::assertResponseStatusCodeSame(400);
        self::assertSame('{"result":"Salary is empty!"}', $response);
    }

    public function testNewCountryError(): void
    {
        $requestData = ["name" => "PHP", "salary" => "5000", "country" => ""];
        $response = $this->new($requestData);
        self::assertResponseStatusCodeSame(400);
        self::assertSame('{"result":"Country is empty!"}', $response);
    }

}
