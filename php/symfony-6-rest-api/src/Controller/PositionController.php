<?php

namespace App\Controller;

use App\Entity\Position;
use App\Form\PositionType;
use App\Repository\PositionRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/position')]
class PositionController extends AbstractController
{

    #[Route('/new', name:'app_position_new', methods:'POST')]
function new (Request $request, PositionRepository $positionRepository): JsonResponse {
    $response = null;
    try {
        $parameters = json_decode($request->get('body'), true);
        //var_dump($parameters);
        if (empty($parameters["name"])) {
            throw new Exception("Name is empty!");
        }
        if (empty($parameters["salary"])) {
            throw new Exception("Salary is empty!");
        }
        if (empty($parameters["country"])) {
            throw new Exception("Country is empty!");
        }

        $position = new Position();
        $position->setName($parameters["name"]);
        $position->setSalary($parameters["salary"]);
        $position->setCountry($parameters["country"]);
        $positionRepository->save($position, true);
        to be continue here name is unique and refuse as new
        //var_dump($position);

        $response = new JsonResponse(['result' => "OK"]);
        return $response;
    } catch (Exception $e) {
        //$retVal = $this->exceptionError($request->query, $e->getFile(), $e->getMessage(), $e->getLine(), $e->getTrace());
        $response = new JsonResponse(['result' => $e->getMessage()], 400);
        return $response;
    }
} //new

#[Route('/{id}/edit', name:'app_position_edit', methods:['GET', 'POST'])]
function edit(Request $request, Position $position, PositionRepository $positionRepository): Response
    {
    $form = $this->createForm(PositionType::class, $position);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $positionRepository->save($position, true);

        return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('position/edit.html.twig', [
        'position' => $position,
        'form' => $form,
    ]);
}

#[Route('/{id}', name:'app_position_delete', methods:['POST'])]
function delete(Request $request, Position $position, PositionRepository $positionRepository): Response
    {
    if ($this->isCsrfTokenValid('delete' . $position->getId(), $request->request->get('_token'))) {
        $positionRepository->remove($position, true);
    }

    return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
}
}
