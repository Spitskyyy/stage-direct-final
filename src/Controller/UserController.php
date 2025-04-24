<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $limit = 10;
        $page = (int) $request->query->get('page', 1);
        $searchTerm = $request->query->get('search', '');

        $criteria = [
            'isVerified' => true,
            'is_verified_user' => true,
        ];

        if (!empty($searchTerm)) {
            // Adaptable selon la recherche voulue
            $criteria['lastname'] = $searchTerm;
        }

        $totalRecords = $userRepository->count($criteria);
        $totalPages = (int) ceil($totalRecords / $limit);
        $offset = ($page - 1) * $limit;

        $users = $userRepository->findBy($criteria, null, $limit, $offset);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'search_term' => $searchTerm,
        ]);
    }

    #[Route('/pending', name: 'app_user_pending', methods: ['GET'])]
    public function pending(Request $request, UserRepository $userRepository): Response
    {
        $pendingUsers = $userRepository->findBy([
            'isVerified' => true,
            'is_verified_user' => false
        ]);

        return $this->render('user/pending.html.twig', [
            'pendingUsers' => $pendingUsers,
        ]);
    }

    #[Route('/user/{id}/verify', name: 'app_user_verify', methods: ['GET'])]
    public function verify(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsVerifiedUser(true);
        $em->flush();
    
        return $this->redirectToRoute('app_user_index');
    }
    
    #[Route('/user/{id}/refuse', name: 'app_user_refuse', methods: ['GET'])]
    public function refuse(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
    
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MODERATOR')]
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
    #[IsGranted('ROLE_MODERATOR')]
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
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
