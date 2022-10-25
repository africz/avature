<?php

namespace App\Controller;

use App\Entity\Position;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class JobPostController extends AbstractController
{
    #[Route('/job/post', name:'app_job_post')]
    function index(ManagerRegistry $doctrine): JsonResponse
    {
        $positions = $doctrine
            ->getRepository(Position::class)
            ->findAll();
        $data = [];

        foreach ($positions as $position) {
            $data[] = [
                'id' => $position->getId(),
                'name' => $position->getName(),
                'salary' => $position->getSalary(),
            ];
        }
        return $this->json($data);
    }//index

    #[Route('/job/post/new', name:'app_job_post_new')]
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $position = new Position();
        $position->setName($request->request->get('name'));
        $position->setSalary($request->request->get('salary'));
        $entityManager->persist($position);
        $entityManager->flush();
        return $this->json('Created new project successfully with id ' . $position->getId());
    }

}//JobPostController

//     /**
//      * @Route("/project", name="project_new", methods={"POST"})
//      */
//     public function new(ManagerRegistry $doctrine, Request $request): Response
//     {
//         $entityManager = $doctrine->getManager();

//         $project = new Project();
//         $project->setName($request->request->get('name'));
//         $project->setDescription($request->request->get('description'));

//         $entityManager->persist($project);
//         $entityManager->flush();

//         return $this->json('Created new project successfully with id ' . $project->getId());
//     }

//     /**
//      * @Route("/project/{id}", name="project_show", methods={"GET"})
//      */
//     public function show(ManagerRegistry $doctrine, int $id): Response
//     {
//         $project = $doctrine->getRepository(Project::class)->find($id);

//         if (!$project) {

//             return $this->json('No project found for id' . $id, 404);
//         }

//         $data =  [
//             'id' => $project->getId(),
//             'name' => $project->getName(),
//             'description' => $project->getDescription(),
//         ];

//         return $this->json($data);
//     }

//     /**
//      * @Route("/project/{id}", name="project_edit", methods={"PUT"})
//      */
//     public function edit(ManagerRegistry $doctrine, Request $request, int $id): Response
//     {
//         $entityManager = $doctrine->getManager();
//         $project = $entityManager->getRepository(Project::class)->find($id);

//         if (!$project) {
//             return $this->json('No project found for id' . $id, 404);
//         }

//         $project->setName($request->request->get('name'));
//         $project->setDescription($request->request->get('description'));
//         $entityManager->flush();

//         $data =  [
//             'id' => $project->getId(),
//             'name' => $project->getName(),
//             'description' => $project->getDescription(),
//         ];

//         return $this->json($data);
//     }

//     /**
//      * @Route("/project/{id}", name="project_delete", methods={"DELETE"})
//      */
//     public function delete(ManagerRegistry $doctrine, int $id): Response
//     {
//         $entityManager = $doctrine->getManager();
//         $project = $entityManager->getRepository(Project::class)->find($id);

//         if (!$project) {
//             return $this->json('No project found for id' . $id, 404);
//         }

//         $entityManager->remove($project);
//         $entityManager->flush();

//         return $this->json('Deleted a project successfully with id ' . $id);
//     }
// }
