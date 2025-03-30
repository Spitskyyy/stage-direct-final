<?php

namespace App\Controller;

use App\Entity\ActivityList;
use App\Form\ActivityListType;
use App\Repository\ActivityListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activity/list')]
final class ActivityListController extends AbstractController
{
    #[Route(name: 'app_activity_list_index', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]
    public function index(ActivityListRepository $activityListRepository): Response
    {
        return $this->render('activity_list/index.html.twig', [
            'activity_lists' => $activityListRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_activity_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activityList = new ActivityList();
        $form = $this->createForm(ActivityListType::class, $activityList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($activityList);
            $entityManager->flush();

            return $this->redirectToRoute('app_activity_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activity_list/new.html.twig', [
            'activity_list' => $activityList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_activity_list_show', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]

    public function show(ActivityList $activityList): Response
    {
        return $this->render('activity_list/show.html.twig', [
            'activity_list' => $activityList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_activity_list_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')]

    public function edit(Request $request, ActivityList $activityList, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActivityListType::class, $activityList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_activity_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activity_list/edit.html.twig', [
            'activity_list' => $activityList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_activity_list_delete', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]

    public function delete(Request $request, ActivityList $activityList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$activityList->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($activityList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_activity_list_index', [], Response::HTTP_SEE_OTHER);
    }
}
