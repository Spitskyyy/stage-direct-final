<?php

namespace App\Tests\Controller;

use App\Entity\Internship;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InternshipControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $internshipRepository;
    private string $path = '/internship/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->internshipRepository = $this->manager->getRepository(Internship::class);

        foreach ($this->internshipRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Internship index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'internship[title]' => 'Testing',
            'internship[start_date]' => 'Testing',
            'internship[end_date]' => 'Testing',
            'internship[is_verified]' => 'Testing',
            'internship[intern]' => 'Testing',
            'internship[school]' => 'Testing',
            'internship[company]' => 'Testing',
            'internship[visitreport]' => 'Testing',
            'internship[activitylist]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->internshipRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Internship();
        $fixture->setTitle('My Title');
        $fixture->setStart_date('My Title');
        $fixture->setEnd_date('My Title');
        $fixture->setIs_verified('My Title');
        $fixture->setIntern('My Title');
        $fixture->setSchool('My Title');
        $fixture->setCompany('My Title');
        $fixture->setVisitreport('My Title');
        $fixture->setActivitylist('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Internship');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Internship();
        $fixture->setTitle('Value');
        $fixture->setStart_date('Value');
        $fixture->setEnd_date('Value');
        $fixture->setIs_verified('Value');
        $fixture->setIntern('Value');
        $fixture->setSchool('Value');
        $fixture->setCompany('Value');
        $fixture->setVisitreport('Value');
        $fixture->setActivitylist('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'internship[title]' => 'Something New',
            'internship[start_date]' => 'Something New',
            'internship[end_date]' => 'Something New',
            'internship[is_verified]' => 'Something New',
            'internship[intern]' => 'Something New',
            'internship[school]' => 'Something New',
            'internship[company]' => 'Something New',
            'internship[visitreport]' => 'Something New',
            'internship[activitylist]' => 'Something New',
        ]);

        self::assertResponseRedirects('/internship/');

        $fixture = $this->internshipRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getStart_date());
        self::assertSame('Something New', $fixture[0]->getEnd_date());
        self::assertSame('Something New', $fixture[0]->getIs_verified());
        self::assertSame('Something New', $fixture[0]->getIntern());
        self::assertSame('Something New', $fixture[0]->getSchool());
        self::assertSame('Something New', $fixture[0]->getCompany());
        self::assertSame('Something New', $fixture[0]->getVisitreport());
        self::assertSame('Something New', $fixture[0]->getActivitylist());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Internship();
        $fixture->setTitle('Value');
        $fixture->setStart_date('Value');
        $fixture->setEnd_date('Value');
        $fixture->setIs_verified('Value');
        $fixture->setIntern('Value');
        $fixture->setSchool('Value');
        $fixture->setCompany('Value');
        $fixture->setVisitreport('Value');
        $fixture->setActivitylist('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/internship/');
        self::assertSame(0, $this->internshipRepository->count([]));
    }
}
