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
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $limit = 10;
        $page = max(1, $request->query->getInt('page', 1));
        $offset = ($page - 1) * $limit;

        // Récupération des paramètres de recherche
        $searchTerms = $request->query->all('search');
        $searchFields = $request->query->all('search_field');

        // Construction de la requête
        $queryBuilder = $entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->where('1=1');

        // Application des filtres de recherche
        if (!empty($searchTerms) && !empty($searchFields)) {
            foreach ($searchTerms as $index => $term) {
                if (!empty($term) && isset($searchFields[$index])) {
                    switch ($searchFields[$index]) {
                        case 'email':
                            $queryBuilder->andWhere('u.email LIKE :email' . $index)
                                ->setParameter('email' . $index, "%{$term}%");
                            break;
                        case 'firstname':
                            $queryBuilder->andWhere('u.firstname LIKE :firstname' . $index)
                                ->setParameter('firstname' . $index, "%{$term}%");
                            break;
                        case 'lastname':
                            $queryBuilder->andWhere('u.lastname LIKE :lastname' . $index)
                                ->setParameter('lastname' . $index, "%{$term}%");
                            break;
                        case 'grade':
                            $queryBuilder->andWhere('u.grade LIKE :grade' . $index)
                                ->setParameter('grade' . $index, "%{$term}%");
                            break;
                        case 'speciality':
                            $queryBuilder->andWhere('u.speciality LIKE :speciality' . $index)
                                ->setParameter('speciality' . $index, "%{$term}%");
                            break;
                        case 'isVerified':
                            $isVerified = strtolower($term) === 'yes' || $term === '1';
                            $queryBuilder->andWhere('u.isVerified = :isVerified' . $index)
                                ->setParameter('isVerified' . $index, $isVerified);
                            break;
                    }
                }
            }
        }

        // Compte total pour la pagination
        $totalRecords = count($queryBuilder->getQuery()->getResult());
        $totalPages = ceil($totalRecords / $limit);

        // Ajout de la pagination
        $users = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords
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
