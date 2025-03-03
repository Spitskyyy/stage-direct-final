<?php

namespace App\Controller;

use App\Entity\Grade;
use App\Form\GradeType;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/grade')]
final class GradeController extends AbstractController
{
    #[Route(name: 'app_grade_index', methods: ['GET'])]
    public function index(GradeRepository $gradeRepository): Response
    {
        return $this->render('grade/index.html.twig', [
            'grades' => $gradeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_grade_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $grade = new Grade();
        $form = $this->createForm(GradeType::class, $grade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($grade);
            $entityManager->flush();

            return $this->redirectToRoute('app_grade_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grade/new.html.twig', [
            'grade' => $grade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grade_show', methods: ['GET'])]
    public function show(Grade $grade): Response
    {
        return $this->render('grade/show.html.twig', [
            'grade' => $grade,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_grade_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Grade $grade, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GradeType::class, $grade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_grade_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grade/edit.html.twig', [
            'grade' => $grade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grade_delete', methods: ['POST'])]
    public function delete(Request $request, Grade $grade, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$grade->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($grade);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_grade_index', [], Response::HTTP_SEE_OTHER);
    }
}
