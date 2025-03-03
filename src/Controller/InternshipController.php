<?php

namespace App\Controller;

use App\Entity\Internship;
use App\Form\InternshipType;
use App\Repository\InternshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/internship')]
final class InternshipController extends AbstractController
{
    #[Route(name: 'app_internship_index', methods: ['GET'])]
    public function index(InternshipRepository $internshipRepository): Response
    {
        return $this->render('internship/index.html.twig', [
            'internships' => $internshipRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_internship_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $internship = new Internship();
        $form = $this->createForm(InternshipType::class, $internship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($internship);
            $entityManager->flush();

            return $this->redirectToRoute('app_internship_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('internship/new.html.twig', [
            'internship' => $internship,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_internship_show', methods: ['GET'])]
    public function show(Internship $internship): Response
    {
        return $this->render('internship/show.html.twig', [
            'internship' => $internship,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_internship_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Internship $internship, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InternshipType::class, $internship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_internship_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('internship/edit.html.twig', [
            'internship' => $internship,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_internship_delete', methods: ['POST'])]
    public function delete(Request $request, Internship $internship, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$internship->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($internship);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_internship_index', [], Response::HTTP_SEE_OTHER);
    }
}
