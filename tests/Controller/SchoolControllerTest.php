<?php

namespace App\Tests\Controller;

use App\Entity\School;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SchoolControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $schoolRepository;
    private string $path = '/school/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->schoolRepository = $this->manager->getRepository(School::class);

        foreach ($this->schoolRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('School index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'school[name]' => 'Testing',
            'school[address]' => 'Testing',
            'school[zip]' => 'Testing',
            'school[city]' => 'Testing',
            'school[phone]' => 'Testing',
            'school[email]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->schoolRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new School();
        $fixture->setName('My Title');
        $fixture->setAddress('My Title');
        $fixture->setZip('My Title');
        $fixture->setCity('My Title');
        $fixture->setPhone('My Title');
        $fixture->setEmail('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('School');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new School();
        $fixture->setName('Value');
        $fixture->setAddress('Value');
        $fixture->setZip('Value');
        $fixture->setCity('Value');
        $fixture->setPhone('Value');
        $fixture->setEmail('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'school[name]' => 'Something New',
            'school[address]' => 'Something New',
            'school[zip]' => 'Something New',
            'school[city]' => 'Something New',
            'school[phone]' => 'Something New',
            'school[email]' => 'Something New',
        ]);

        self::assertResponseRedirects('/school/');

        $fixture = $this->schoolRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getAddress());
        self::assertSame('Something New', $fixture[0]->getZip());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getPhone());
        self::assertSame('Something New', $fixture[0]->getEmail());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new School();
        $fixture->setName('Value');
        $fixture->setAddress('Value');
        $fixture->setZip('Value');
        $fixture->setCity('Value');
        $fixture->setPhone('Value');
        $fixture->setEmail('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/school/');
        self::assertSame(0, $this->schoolRepository->count([]));
    }
}
