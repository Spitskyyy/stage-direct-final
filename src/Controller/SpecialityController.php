<?php

namespace App\Controller;

use App\Entity\Speciality;
use App\Form\SpecialityType;
use App\Repository\SpecialityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/speciality')]
final class SpecialityController extends AbstractController
{

    #[Route(name: 'app_speciality_index', methods: ['GET'])]
    public function index(Request $request, SpecialityRepository $specialityRepository): Response
    {
        //return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        $limit = 10; // Nombre d'éléments par page
        $page = (int) $request->query->get('page', 1); // Page actuelle, par défaut 1
        $searchTerm = $request->query->get('search', ''); // Paramètre de recherche, par défaut ''

        $criteria = [];
        // Récupération du nombre total d'éléments (en tenant compte du filtre de recherche)
        $totalRecords = $specialityRepository->count($criteria);
        $totalPages = (int) ceil($totalRecords / $limit); // Nombre total de pages

        if (empty($searchTerm)) {
            // Pas de recherche, on affiche tout
            $specialities = $specialityRepository->findAll($page);
        } else {
            // Constructeur de la requête avec recherche

            if ($searchTerm) {
                $criteria['name'] = $searchTerm; // Par exemple, rechercher par nom
            }

            // Récupération des utilisateurs pour la page actuelle, en tenant compte de la recherche
            $specialities = $specialityRepository->searchBy($criteria);
        }

        return $this->render('speciality/index.html.twig', [
            'specialities' => $specialities,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'search_term' => $searchTerm, // Passer le terme de recherche au template
        ]);
    }

    #[Route('/new', name: 'app_speciality_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $speciality = new Speciality();
        $form = $this->createForm(SpecialityType::class, $speciality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($speciality);
            $entityManager->flush();

            return $this->redirectToRoute('app_speciality_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('speciality/new.html.twig', [
            'speciality' => $speciality,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_speciality_show', methods: ['GET'])]
    public function show(Request $request, Speciality $speciality): Response
    {
        $form = $this->createForm(SpecialityType::class, $speciality);
        $form->handleRequest($request);

        return $this->render('speciality/show.html.twig', [
            'speciality' => $speciality,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_speciality_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Speciality $speciality, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SpecialityType::class, $speciality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_speciality_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('speciality/edit.html.twig', [
            'speciality' => $speciality,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_speciality_delete', methods: ['POST'])]
    public function delete(Request $request, Speciality $speciality, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$speciality->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($speciality);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_speciality_index', [], Response::HTTP_SEE_OTHER);
    }
}
