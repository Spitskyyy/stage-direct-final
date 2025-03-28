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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route(name: 'app_company_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $queryBuilder = $entityManager->getRepository(Company::class)->createQueryBuilder('c');

        for ($i = 1; $i <= 3; $i++) {
            $search = $request->query->get('search' . $i);
            $searchField = $request->query->get('search_field' . $i);

            if ($search && $searchField) {
                $queryBuilder
                    ->andWhere("c.$searchField LIKE :search$i")
                    ->setParameter("search$i", '%' . $search . '%');
            }
        }

        $companies = $queryBuilder->getQuery()->getResult();

        return $this->render('company/index.html.twig', [
            'companies' => $companies,
            'search1' => $request->query->get('search1'),
            'search2' => $request->query->get('search2'),
            'search3' => $request->query->get('search3'),
            'search_field1' => $request->query->get('search_field1'),
            'search_field2' => $request->query->get('search_field2'),
            'search_field3' => $request->query->get('search_field3'),
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index');
        }

        return $this->render('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Company $company): Response
    {
        return $this->render('company/show.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
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