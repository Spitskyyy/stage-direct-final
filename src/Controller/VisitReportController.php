<?php

namespace App\Controller;

use App\Entity\VisitReport;
use App\Form\VisitReportType;
use App\Repository\VisitReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/visit/report')]
final class VisitReportController extends AbstractController
{
    #[Route(name: 'app_visit_report_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]

    public function index(VisitReportRepository $visitReportRepository): Response
    {
        return $this->render('visit_report/index.html.twig', [
            'visit_reports' => $visitReportRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_visit_report_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]

    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $visitReport = new VisitReport();
        $visitReport->setCreator($this->getUser());
        $form = $this->createForm(VisitReportType::class, $visitReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($visitReport);
            $entityManager->flush();

            return $this->redirectToRoute('app_visit_report_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visit_report/new.html.twig', [
            'visit_report' => $visitReport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visit_report_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]

    public function show(VisitReport $visitReport): Response
    {
        return $this->render('visit_report/show.html.twig', [
            'visit_report' => $visitReport,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_visit_report_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]

    public function edit(Request $request, VisitReport $visitReport, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('edit', $visitReport)) {
            throw new AccessDeniedException('Accès refusé.');
        }
        $form = $this->createForm(VisitReportType::class, $visitReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_visit_report_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visit_report/edit.html.twig', [
            'visit_report' => $visitReport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visit_report_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]

    public function delete(Request $request, VisitReport $visitReport, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('edit', $visitReport)) {
            throw new AccessDeniedException('Accès refusé.');
        }
        if ($this->isCsrfTokenValid('delete'.$visitReport->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visitReport);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visit_report_index', [], Response::HTTP_SEE_OTHER);
    }
}
