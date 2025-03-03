<?php

namespace App\Tests\Controller;

use App\Entity\ActivityList;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ActivityListControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $activityListRepository;
    private string $path = '/activity/list/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->activityListRepository = $this->manager->getRepository(ActivityList::class);

        foreach ($this->activityListRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ActivityList index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'activity_list[contained]' => 'Testing',
            'activity_list[is_verified]' => 'Testing',
            'activity_list[internship]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->activityListRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new ActivityList();
        $fixture->setContained('My Title');
        $fixture->setIs_verified('My Title');
        $fixture->setInternship('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ActivityList');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new ActivityList();
        $fixture->setContained('Value');
        $fixture->setIs_verified('Value');
        $fixture->setInternship('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'activity_list[contained]' => 'Something New',
            'activity_list[is_verified]' => 'Something New',
            'activity_list[internship]' => 'Something New',
        ]);

        self::assertResponseRedirects('/activity/list/');

        $fixture = $this->activityListRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getContained());
        self::assertSame('Something New', $fixture[0]->getIs_verified());
        self::assertSame('Something New', $fixture[0]->getInternship());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new ActivityList();
        $fixture->setContained('Value');
        $fixture->setIs_verified('Value');
        $fixture->setInternship('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/activity/list/');
        self::assertSame(0, $this->activityListRepository->count([]));
    }
}
