<?php

namespace App\Controller;

use App\Entity\School;
use App\Form\SchoolType;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/school')]
final class SchoolController extends AbstractController
{
    #[Route(name: 'app_school_index', methods: ['GET'])]
    public function index(Request $request, SchoolRepository $schoolRepository): Response
    {
        $limit = 10; // Nombre d'éléments par page
        $page = (int) $request->query->get('page', 1); // Page actuelle, par défaut 1
        $searchTerm = $request->query->get('search', ''); // Paramètre de recherche, par défaut ''

        $criteria = [];
        // Récupération du nombre total d'éléments (en tenant compte du filtre de recherche)
        $totalRecords = $schoolRepository->count($criteria);
        $totalPages = (int) ceil($totalRecords / $limit); // Nombre total de pages

        if (empty($searchTerm)) {
            // Calcul de l'offset pour la pagination
            $offset = ($page - 1) * $limit;
            $schools = $schoolRepository->findBy([], null, $limit, $offset);
        } else {
            if ($searchTerm) {
                $criteria['name'] = $searchTerm; // Exemple de critère
            }
            $schools = $schoolRepository->searchBy($criteria);
        }
        
        return $this->render('school/index.html.twig', [
            'schools' => $schools,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'search_term' => $searchTerm, // Passer le terme de recherche au template
        ]);
    }

    #[Route('/new', name: 'app_school_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $school = new School();
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($school);
            $entityManager->flush();

            return $this->redirectToRoute('app_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('school/new.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_school_show', methods: ['GET'])]
    public function show(School $school): Response
    {
        return $this->render('school/show.html.twig', [
            'school' => $school,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_school_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('school/edit.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_school_delete', methods: ['POST'])]
    public function delete(Request $request, School $school, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$school->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($school);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_school_index', [], Response::HTTP_SEE_OTHER);
    }
}
