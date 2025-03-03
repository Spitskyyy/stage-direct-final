<?php

namespace App\Tests\Controller;

use App\Entity\VisitReport;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class VisitReportControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $visitReportRepository;
    private string $path = '/visit/report/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->visitReportRepository = $this->manager->getRepository(VisitReport::class);

        foreach ($this->visitReportRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('VisitReport index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'visit_report[contained]' => 'Testing',
            'visit_report[is_verified]' => 'Testing',
            'visit_report[internship]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->visitReportRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new VisitReport();
        $fixture->setContained('My Title');
        $fixture->setIs_verified('My Title');
        $fixture->setInternship('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('VisitReport');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new VisitReport();
        $fixture->setContained('Value');
        $fixture->setIs_verified('Value');
        $fixture->setInternship('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'visit_report[contained]' => 'Something New',
            'visit_report[is_verified]' => 'Something New',
            'visit_report[internship]' => 'Something New',
        ]);

        self::assertResponseRedirects('/visit/report/');

        $fixture = $this->visitReportRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getContained());
        self::assertSame('Something New', $fixture[0]->getIs_verified());
        self::assertSame('Something New', $fixture[0]->getInternship());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new VisitReport();
        $fixture->setContained('Value');
        $fixture->setIs_verified('Value');
        $fixture->setInternship('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/visit/report/');
        self::assertSame(0, $this->visitReportRepository->count([]));
    }
}
