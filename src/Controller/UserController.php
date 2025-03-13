<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        //return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        $limit = 10; // Nombre d'éléments par page
        $page = (int) $request->query->get('page', 1); // Page actuelle, par défaut 1
        $searchTerm = $request->query->get('search', ''); // Paramètre de recherche, par défaut ''

        $criteria = [];
        // Récupération du nombre total d'éléments (en tenant compte du filtre de recherche)
        $totalRecords = $userRepository->count($criteria);
        $totalPages = (int) ceil($totalRecords / $limit); // Nombre total de pages

        if (empty($searchTerm)) {
            // Pas de recherche, on affiche tout
            $users = $userRepository->findAll($page);
        } else {
            // Constructeur de la requête avec recherche

            if ($searchTerm) {
                $criteria['name'] = $searchTerm; // Par exemple, rechercher par nom
            }

            // Récupération des utilisateurs pour la page actuelle, en tenant compte de la recherche
            $users = $userRepository->searchBy($criteria);
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'search_term' => $searchTerm, // Passer le terme de recherche au template
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->redirectToRoute('app_register');
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
