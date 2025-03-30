<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    $queryBuilder = $entityManager->getRepository(Company::class)->createQueryBuilder('c');

    $searchTerms = $request->query->all('search');
    $searchFields = $request->query->all('search_field');

    // Liste des champs numériques qui ne supportent pas LIKE
    $numericFields = ['phone', 'zip'];

    if ($searchTerms && $searchFields) {
        foreach ($searchTerms as $index => $term) {
            if (!empty($term) && isset($searchFields[$index])) {
                $field = $searchFields[$index];

                // Vérifier si le champ est numérique
                if (in_array($field, $numericFields)) {
                    $queryBuilder
                        ->andWhere("c.$field = :search$index") // Utiliser "=" au lieu de LIKE
                        ->setParameter("search$index", $term);
                } else {
                    $queryBuilder
                        ->andWhere("c.$field LIKE :search$index")
                        ->setParameter("search$index", "%{$term}%");
                }
            }
        }
    }

    $companies = $queryBuilder->getQuery()->getResult();

    return $this->render('company/index.html.twig', [
        'companies' => $companies,
        'search' => $searchTerms,
        'search_field' => $searchFields,
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

            return $this->redirectToRoute('company_pending'); // Redirection vers la page d’attente
        }

        return $this->render('company/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/company/pending', name: 'company_pending')]
    public function pending(EntityManagerInterface $entityManager): Response
    {
        $company = $entityManager->getRepository(Company::class)->findBy(['is_verified' => false]);

        return $this->render('company/pending.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/verify/{id}', name: 'company_verify')]
    public function verify(Company $company, EntityManagerInterface $entityManager): Response
    {
        $company->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Stage vérifié avec succès.');

        return $this->redirectToRoute('app_company_index');
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
    // #[IsGranted('ROLE_STUDENT')]
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
    public function exportToExcel(CompanyRepository $companyRepository): StreamedResponse
    {
        $this->denyAccessUnlessVerified($this->getUser());

        $companies = $companyRepository->findAll();

        $response = new StreamedResponse(function () use ($companies) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'Nom');
            $sheet->setCellValue('C1', 'Adresse');
            $sheet->setCellValue('D1', 'Ville');
            $sheet->setCellValue('E1', 'Code postal');
            $sheet->setCellValue('F1', 'Pays');
            $sheet->setCellValue('G1', 'Téléphone');
            $sheet->setCellValue('H1', 'Email');

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
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="companies.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}