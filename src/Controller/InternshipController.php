<?php

namespace App\Controller;

use App\Entity\Internship;
use App\Form\InternshipType;
use App\Entity\Company; // Ajouter cette ligne
use App\Entity\VisitReport;
use App\Entity\ActivityList;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\InternshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/internship')]
final class InternshipController extends AbstractController
{
    #[Route('/', name: 'app_internship_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $limit = 10;
        $page = max(1, $request->query->getInt('page', 1));
        $offset = ($page - 1) * $limit;

        // Récupération des paramètres de recherche
        $searchTerms = $request->query->all('search');
        $searchFields = $request->query->all('search_field');

        // Construction de la requête
        $queryBuilder = $entityManager->getRepository(Internship::class)->createQueryBuilder('i')
            ->leftJoin('i.company', 'c')
            ->leftJoin('i.school', 's')
            ->where('i.is_verified = true');

        // Application des filtres de recherche
        if (!empty($searchTerms) && !empty($searchFields)) {
            foreach ($searchTerms as $index => $term) {
                if (!empty($term) && isset($searchFields[$index])) {
                    switch ($searchFields[$index]) {
                        case 'title':
                            $queryBuilder->andWhere('i.title LIKE :searchTerm' . $index)
                                ->setParameter('searchTerm' . $index, "%{$term}%");
                            break;
                        case 'startDate':
                            try {
                                $date = new \DateTime($term);
                                $queryBuilder->andWhere('i.start_date = :startDate' . $index)
                                    ->setParameter('startDate' . $index, $date);
                            } catch (\Exception $e) {
                                // Gérer l'erreur de date invalide
                            }
                            break;
                        case 'endDate':
                            try {
                                $date = new \DateTime($term);
                                $queryBuilder->andWhere('i.end_date = :endDate' . $index)
                                    ->setParameter('endDate' . $index, $date);
                            } catch (\Exception $e) {
                                // Gérer l'erreur de date invalide
                            }
                            break;
                        case 'company':
                            $queryBuilder->andWhere('c.name LIKE :company' . $index)
                                ->setParameter('company' . $index, "%{$term}%");
                            break;
                        case 'school':
                            $queryBuilder->andWhere('s.name LIKE :school' . $index)
                                ->setParameter('school' . $index, "%{$term}%");
                            break;
                    }
                }
            }
        }

        // Compte total pour la pagination
        $totalRecords = count($queryBuilder->getQuery()->getResult());
        $totalPages = ceil($totalRecords / $limit);

        // Ajout de la pagination
        $internships = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('internship/index.html.twig', [
            'internships' => $internships,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'search_terms' => $searchTerms,
            'search_fields' => $searchFields
        ]);
    }

    #[Route('/new', name: 'app_internship_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $internship = new Internship();
        
        // Définir automatiquement l'utilisateur connecté comme stagiaire
        $internship->setIntern($this->getUser());
        $internship->setCreator($this->getUser());

        // Récupérer l'entreprise depuis l'URL si elle est fournie
        $companyId = $request->query->get('company');
        if ($companyId) {
            $company = $entityManager->getRepository(Company::class)->find($companyId);
            if ($company) {
                $internship->setCompany($company);
            }
        }
        
        $form = $this->createForm(InternshipType::class, $internship, [
            'is_new' => true  // Passer une option pour indiquer que c'est un nouveau stage
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créer un nouveau rapport de visite vide
            $visitReport = new VisitReport();
            $visitReport->setIsVerified(false);
            $visitReport->setCreator($this->getUser());
            $visitReport->setInternship($internship);
            $entityManager->persist($visitReport);

            // Créer une nouvelle liste d'activités vide
            $activityList = new ActivityList();
            $activityList->setIsVerified(false);
            $activityList->setCreator($this->getUser());
            $activityList->setInternship($internship);
            $entityManager->persist($activityList);

            // Lier les objets au stage
            $internship->setVisitreport($visitReport);
            $internship->setActivitylist($activityList);
            $internship->setIsVerified(false);

            $entityManager->persist($internship);
            $entityManager->flush();

            $this->addFlash('success', 'Stage créé avec succès et en attente de validation.');
            return $this->redirectToRoute('app_internship_index');
        }

        return $this->render('internship/new.html.twig', [
            'internship' => $internship,
            'form' => $form,
        ]);
    }

    #[Route('/pending', name: 'app_internship_pending', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[Route('/pending', name: 'internship_pending')]
    #[IsGranted('ROLE_TEACHER')]
    public function pending(EntityManagerInterface $entityManager): Response
    {
        $internships = $entityManager->getRepository(Internship::class)
            ->findBy(['is_verified' => false]);

        return $this->render('internship/pending.html.twig', [
            'internships' => $internships
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

    #[Route('/refuse/{id}', name: 'internship_refuse')]
    #[IsGranted('ROLE_TEACHER')]
    public function refuse(Internship $internship, EntityManagerInterface $entityManager): Response
    {
        // Supprime le rapport de visite et la liste d'activités associés
        if ($internship->getVisitreport()) {
            $entityManager->remove($internship->getVisitreport());
        }
        if ($internship->getActivitylist()) {
            $entityManager->remove($internship->getActivitylist());
        }
        
        // Supprime le stage
        $entityManager->remove($internship);
        $entityManager->flush();

        $this->addFlash('success', 'Stage refusé et supprimé avec succès.');

        return $this->redirectToRoute('app_internship_index');
    }

    #[Route('/export-pdf', name: 'app_internship_export_pdf', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function exportPdf(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération des paramètres de recherche
        $searchTerms = $request->query->all('search');
        $searchFields = $request->query->all('search_field');

        // Construction de la requête
        $queryBuilder = $entityManager->getRepository(Internship::class)->createQueryBuilder('i')
            ->leftJoin('i.company', 'c')
            ->leftJoin('i.school', 's')
            ->where('i.is_verified = true');

        // Application des filtres de recherche
        if (!empty($searchTerms) && !empty($searchFields)) {
            foreach ($searchTerms as $index => $term) {
                if (!empty($term) && isset($searchFields[$index])) {
                    switch ($searchFields[$index]) {
                        case 'title':
                            $queryBuilder->andWhere('i.title LIKE :searchTerm' . $index)
                                ->setParameter('searchTerm' . $index, "%{$term}%");
                            break;
                        case 'startDate':
                            try {
                                $date = new \DateTime($term);
                                $queryBuilder->andWhere('i.start_date = :startDate' . $index)
                                    ->setParameter('startDate' . $index, $date);
                            } catch (\Exception $e) {
                                // Gérer l'erreur de date invalide
                            }
                            break;
                        case 'endDate':
                            try {
                                $date = new \DateTime($term);
                                $queryBuilder->andWhere('i.end_date = :endDate' . $index)
                                    ->setParameter('endDate' . $index, $date);
                            } catch (\Exception $e) {
                                // Gérer l'erreur de date invalide
                            }
                            break;
                        case 'company':
                            $queryBuilder->andWhere('c.name LIKE :company' . $index)
                                ->setParameter('company' . $index, "%{$term}%");
                            break;
                        case 'school':
                            $queryBuilder->andWhere('s.name LIKE :school' . $index)
                                ->setParameter('school' . $index, "%{$term}%");
                            break;
                    }
                }
            }
        }

        $internships = $queryBuilder->getQuery()->getResult();

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $html = $this->renderView('internship/export_pdf.html.twig', [
            'internships' => $internships,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="internships.pdf"'
            ]
        );
    }

    #[Route('/export', name: 'app_internship_export', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function exportToExcel(Request $request, EntityManagerInterface $entityManager): StreamedResponse
    {
        // Récupération des paramètres de recherche
        $searchTerms = $request->query->all('search');
        $searchFields = $request->query->all('search_field');

        // Construction de la requête
        $queryBuilder = $entityManager->getRepository(Internship::class)->createQueryBuilder('i')
            ->leftJoin('i.company', 'c')
            ->leftJoin('i.school', 's')
            ->where('i.is_verified = true');

        // Application des filtres de recherche
        if (!empty($searchTerms) && !empty($searchFields)) {
            foreach ($searchTerms as $index => $term) {
                if (!empty($term) && isset($searchFields[$index])) {
                    switch ($searchFields[$index]) {
                        case 'title':
                            $queryBuilder->andWhere('i.title LIKE :searchTerm' . $index)
                                ->setParameter('searchTerm' . $index, "%{$term}%");
                            break;
                        case 'startDate':
                            try {
                                $date = new \DateTime($term);
                                $queryBuilder->andWhere('i.start_date = :startDate' . $index)
                                    ->setParameter('startDate' . $index, $date);
                            } catch (\Exception $e) {
                                // Gérer l'erreur de date invalide
                            }
                            break;
                        case 'endDate':
                            try {
                                $date = new \DateTime($term);
                                $queryBuilder->andWhere('i.end_date = :endDate' . $index)
                                    ->setParameter('endDate' . $index, $date);
                            } catch (\Exception $e) {
                                // Gérer l'erreur de date invalide
                            }
                            break;
                        case 'company':
                            $queryBuilder->andWhere('c.name LIKE :company' . $index)
                                ->setParameter('company' . $index, "%{$term}%");
                            break;
                        case 'school':
                            $queryBuilder->andWhere('s.name LIKE :school' . $index)
                                ->setParameter('school' . $index, "%{$term}%");
                            break;
                    }
                }
            }
        }

        $internships = $queryBuilder->getQuery()->getResult();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // En-têtes
        $headers = ['ID', 'Title', 'Start Date', 'End Date', 'Intern', 'School', 'Company', 'Activity List', 'Visit Report'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header);
        }
        
        // Données
        $row = 2;
        foreach ($internships as $internship) {
            $sheet->setCellValue('A' . $row, $internship->getId());
            $sheet->setCellValue('B' . $row, $internship->getTitle());
            $sheet->setCellValue('C' . $row, $internship->getStartDate() ? $internship->getStartDate()->format('Y-m-d') : '');
            $sheet->setCellValue('D' . $row, $internship->getEndDate() ? $internship->getEndDate()->format('Y-m-d') : '');
            $sheet->setCellValue('E' . $row, $internship->getIntern() ? $internship->getIntern()->getId() : '');
            $sheet->setCellValue('F' . $row, $internship->getSchool() ? $internship->getSchool()->getId() : '');
            $sheet->setCellValue('G' . $row, $internship->getCompany() ? $internship->getCompany()->getId() : '');
            $sheet->setCellValue('H' . $row, $internship->getActivityList() ? 'Oui' : 'Non');
            $sheet->setCellValue('I' . $row, $internship->getVisitReport() ? 'Oui' : 'Non');
            $row++;
        }

        $response = new StreamedResponse(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="internships.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    #[Route('/{id}', name: 'app_internship_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // <- Maintenant tous les utilisateurs peuvent voir les stages
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
        // Vérifie les permissions avec le voter
        if (!$this->isGranted('edit', $internship)) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(InternshipType::class, $internship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_internship_index');
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
        if (!$this->isGranted('edit', $internship)) {
            throw new AccessDeniedException('Accès refusé.');
        }

        if ($this->isCsrfTokenValid('delete' . $internship->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($internship);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_internship_index');
    }
}
