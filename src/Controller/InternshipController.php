<?php

namespace App\Controller;

use App\Entity\Internship;
use App\Form\InternshipType;
use App\Repository\InternshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/internship')]
final class InternshipController extends AbstractController
{
    #[Route(name: 'app_internship_index', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_USER')]
public function index(Request $request, InternshipRepository $internshipRepository): Response
{
    $limit = 10; // Nombre d'éléments par page
    $page = (int) $request->query->get('page', 1); // Page actuelle, par défaut 1
    $searchTerm = $request->query->get('search', ''); // Paramètre de recherche, par défaut ''

    // Ajout du critère pour ne récupérer que les stages vérifiés
    $criteria = ['is_verified' => true];

    if (!empty($searchTerm)) {
        $criteria['title'] = $searchTerm; // Exemple de critère si le repository supporte la recherche
    }

    // Récupération du nombre total de stages vérifiés
    $totalRecords = $internshipRepository->count($criteria);
    $totalPages = (int) ceil($totalRecords / $limit);

    // Pagination
    $offset = ($page - 1) * $limit;
    $internships = $internshipRepository->findBy($criteria, null, $limit, $offset);

    return $this->render('internship/index.html.twig', [
        'internships' => $internships,
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'search_term' => $searchTerm,
    ]);
}


    #[Route('/new', name: 'app_internship_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]

    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $internship = new Internship();
        $form = $this->createForm(InternshipType::class, $internship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $internship->setIsVerified(false); // Met en attente de validation
            $entityManager->persist($internship);
            $entityManager->flush();

            $this->addFlash('success', 'Votre stage est en attente de validation.');

            return $this->redirectToRoute('internship_pending'); // Redirection vers la page d’attente
        }

        return $this->render('internship/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pending', name: 'internship_pending')]
    #[IsGranted('ROLE_TEACHER')]

    public function pending(EntityManagerInterface $entityManager): Response
    {
        $internships = $entityManager->getRepository(Internship::class)->findBy(['is_verified' => false]);

        return $this->render('internship/pending.html.twig', [
            'internships' => $internships,
        ]);
    }

    #[Route('/verify/{id}', name: 'internship_verify')]
    #[IsGranted('ROLE_TEACHER')]

    public function verify(Internship $internship, EntityManagerInterface $entityManager): Response
    {
        $internship->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Stage vérifié avec succès.');

        return $this->redirectToRoute('app_internship_index');
    }


    #[Route('/{id}', name: 'app_internship_show', methods: ['GET'])]
    #[IsGranted('ROLE_TEACHER')]

    public function show(Internship $internship): Response
    {
        return $this->render('internship/show.html.twig', [
            'internship' => $internship,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_internship_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]

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
    #[IsGranted('ROLE_USER')]

    public function delete(Request $request, Internship $internship, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $internship->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($internship);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_internship_index', [], Response::HTTP_SEE_OTHER);
    }




}
