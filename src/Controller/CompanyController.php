<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
// use Symfony\Component\Security\Http\Attribute\IsGranted;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route(name: 'app_company_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    // #[IsGranted('ROLE_STUDENT')]
    public function index(Request $request, CompanyRepository $companyRepository): Response
    {
        $limit = 10; // Nombre d'éléments par page
        $page = (int) $request->query->get('page', 1); // Page actuelle, par défaut 1
        $searchTerm = $request->query->get('search', ''); // Paramètre de recherche, par défaut ''

        $criteria = [];
        // Récupération du nombre total d'éléments (en tenant compte du filtre de recherche)
        $totalRecords = $companyRepository->count($criteria);
        $totalPages = (int) ceil($totalRecords / $limit); // Nombre total de pages

        if (empty($searchTerm)) {
            // Pas de recherche, on affiche tout
            $companies = $companyRepository->findAll($page);
        } else {
            // Constructeur de la requête avec recherche

            if ($searchTerm) {
                $criteria['name'] = $searchTerm; // Par exemple, rechercher par nom
            }

            // Récupération des entreprises pour la page actuelle, en tenant compte de la recherche
            // $companies = $companyRepository->findBy($criteria, null, $limit, ($page - 1) * $limit);
            $companies = $companyRepository->searchBy($criteria);
        }

        return $this->render('company/index.html.twig', [
            'companies' => $companies,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'search_term' => $searchTerm, // Passer le terme de recherche au template
        ]);
    }


    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    // #[IsGranted('ROLE_STUDENT')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/company/{id}', name: 'app_company_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    // #[IsGranted('ROLE_STUDENT')]
    public function show(Company $company): Response
    {
        return $this->render('company/show.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/company/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    // #[IsGranted('ROLE_STUDENT')]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route(path: '/company/{id}', name: 'app_company_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    // #[IsGranted('ROLE_STUDENT')]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $company->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($company);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'app_company_export', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    // #[IsGranted('ROLE_STUDENT')]
    public function exportToExcel(CompanyRepository $companyRepository): StreamedResponse
    {
        // Récupérer toutes les entreprises
        $companies = $companyRepository->findAll();

        // Créer une réponse "streamed" pour générer le fichier dynamiquement
        $response = new StreamedResponse(function () use ($companies) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Ajouter les en-têtes
            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'Nom');
            $sheet->setCellValue('C1', 'Addresse');
            $sheet->setCellValue('D1', 'Ville');
            $sheet->setCellValue('E1', 'Code postal');
            $sheet->setCellValue('F1', 'Pays');
            $sheet->setCellValue('F1', 'Téléphone');
            $sheet->setCellValue('F1', 'Email');


            // Ajouter les données des entreprises
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
                $sheet->setCellValue('H' . $row, $company->isVerified());
                $row++;
            }

            // Créer l'objet Writer
            $writer = new Xlsx($spreadsheet);

            // Sauvegarder dans php://output
            $writer->save('php://output');
        });

        // Configurer les en-têtes de la réponse
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="companies_export.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

}