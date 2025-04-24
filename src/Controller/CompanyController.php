<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    private function denyAccessUnlessVerified(User $user): void
    {
        if (!$user->isVerified()) {
            throw new AccessDeniedException('Vous devez vérifier votre compte pour accéder à cette page.');
        }
    }

    #[Route('/company/export-pdf', name: 'app_company_export_pdf')]

    public function exportPdf(EntityManagerInterface $entityManager): Response
    {
        // Remplacer getDoctrine() par l'EntityManagerInterface injecté
        $companies = $entityManager->getRepository(Company::class)->findAll();

        // Configure Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        // Générer le HTML pour le PDF
        $html = $this->renderView('company/export_pdf.html.twig', [
            'companies' => $companies,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="companies.pdf"',
            ]
        );
    }

    #[Route(name: 'app_company_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessVerified($this->getUser());

        $limit = 10; // Nombre d'éléments par page
        $page = (int) $request->query->get('page', 1); // Page actuelle
        $offset = ($page - 1) * $limit;

        // Construction de la requête de base
        $queryBuilder = $entityManager->getRepository(Company::class)->createQueryBuilder('c')
            ->where('c.is_verified = true');

        // Gestion des filtres de recherche
        $searchTerms = $request->query->all('search');
        $searchFields = $request->query->all('search_field');
        $numericFields = ['phone', 'zip'];

        if ($searchTerms && $searchFields) {
            foreach ($searchTerms as $index => $term) {
                if (!empty($term) && isset($searchFields[$index])) {
                    $field = $searchFields[$index];
                    if (in_array($field, $numericFields) && $field != "zip" && $field != "phone") {
                        $queryBuilder->andWhere("c.$field = :search$index")
                            ->setParameter("search$index", $term);
                    } else {
                        $queryBuilder->andWhere("c.$field LIKE :search$index")
                            ->setParameter("search$index", "%{$term}%");
                    }
                }
            }
        }

        // Compte total pour la pagination
        $totalRecords = count($queryBuilder->getQuery()->getResult());
        $totalPages = ceil($totalRecords / $limit);

        // Ajout de la pagination à la requête
        $companies = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('company/index.html.twig', [
            'companies' => $companies,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'search_terms' => $searchTerms,
            'search_fields' => $searchFields
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessVerified($this->getUser());

        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company->setIsVerified(false); // Met en attente de validation
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'Votre entreprise est en attente de validation.');

            return $this->redirectToRoute('app_company_index'); // Redirection vers la page d’attente
        }

        return $this->render('company/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/company/pending', name: 'company_pending')]
    #[IsGranted('ROLE_TEACHER')]

    public function pending(EntityManagerInterface $entityManager): Response
    {
        $companies = $entityManager->getRepository(Company::class)->findBy(['is_verified' => false]);

        return $this->render('company/pending.html.twig', [
            'company' => $companies,
        ]);
    }

    #[Route('/verify/{id}', name: 'company_verify')]
    #[IsGranted('ROLE_TEACHER')]

    public function verify(Company $company, EntityManagerInterface $entityManager): Response
    {
        $company->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Stage vérifié avec succès.');

        return $this->redirectToRoute('app_company_index');
    }

    #[Route('/refuse/{id}', name: 'company_refuse')]
    #[IsGranted('ROLE_TEACHER')]
    public function refuse(Company $company, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($company);
        $entityManager->flush();

        $this->addFlash('success', 'Stage refusé et supprimé.');

        return $this->redirectToRoute('company_pending');
    }

    #[Route('/company/{id}', name: 'app_company_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Company $company): Response
    {
        $this->denyAccessUnlessVerified($this->getUser());

        return $this->render('company/show.html.twig', [
            'company' => $company,
        ]);
    }


    #[Route('/company/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    // #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessVerified($this->getUser());

        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index');
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessVerified($this->getUser());

        if ($this->isCsrfTokenValid('delete' . $company->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($company);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_company_index');
    }

    #[Route('/export', name: 'app_company_export', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
public function exportToExcel(Request $request, EntityManagerInterface $entityManager): StreamedResponse
{
    $this->denyAccessUnlessVerified($this->getUser());

    // Récupération des critères de recherche
    $searchTerms = $request->query->all('search');
    $searchFields = $request->query->all('search_field');
    
    // Construction de la requête avec les mêmes filtres que l'index
    $queryBuilder = $entityManager->getRepository(Company::class)->createQueryBuilder('c')
        ->where('c.is_verified = true');

    if ($searchTerms && $searchFields) {
        foreach ($searchTerms as $index => $term) {
            if (!empty($term) && isset($searchFields[$index])) {
                $field = $searchFields[$index];
                if (in_array($field, ['phone', 'zip'])) {
                    $queryBuilder->andWhere("c.$field = :search$index")
                        ->setParameter("search$index", $term);
                } else {
                    $queryBuilder->andWhere("c.$field LIKE :search$index")
                        ->setParameter("search$index", "%{$term}%");
                }
            }
        }
    }

    $companies = $queryBuilder->getQuery()->getResult();

    // Création du fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // En-têtes
    $headers = ['ID', 'Nom', 'Adresse', 'Ville', 'Code Postal', 'Pays', 'Téléphone', 'Email', 'Vérifié'];
    foreach ($headers as $key => $header) {
        $sheet->setCellValue(chr(65 + $key) . '1', $header);
    }
    
    // Données
    $row = 2;
    foreach ($companies as $company) {
        $sheet->setCellValue('A' . $row, $company->getId());
        $sheet->setCellValue('B' . $row, $company->getName());
        $sheet->setCellValue('C' . $row, $company->getAddress());
        $sheet->setCellValue('D' . $row, $company->getCity());
        $sheet->setCellValue('E' . $row, $company->getZip());
        $sheet->setCellValue('F' . $row, $company->getCountry());
        $sheet->setCellValue('G' . $row, $company->getPhone());
        $sheet->setCellValue('H' . $row, $company->getEmail());
        $sheet->setCellValue('I' . $row, $company->isVerified() ? 'Oui' : 'Non'); // Correction ici
        $row++;
    }

    // Création de la réponse
    $response = new StreamedResponse(function() use ($spreadsheet) {
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    });

    // En-têtes HTTP
    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment;filename="companies.xlsx"');
    $response->headers->set('Cache-Control', 'max-age=0');

    return $response;
}
}
