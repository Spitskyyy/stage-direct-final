<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CompanyControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $companyRepository;
    private string $path = '/company/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->companyRepository = $this->manager->getRepository(Company::class);

        foreach ($this->companyRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Company index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'company[name]' => 'Testing',
            'company[address]' => 'Testing',
            'company[city]' => 'Testing',
            'company[zip]' => 'Testing',
            'company[country]' => 'Testing',
            'company[phone]' => 'Testing',
            'company[email]' => 'Testing',
            'company[is_verified]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->companyRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Company();
        $fixture->setName('My Title');
        $fixture->setAddress('My Title');
        $fixture->setCity('My Title');
        $fixture->setZip('My Title');
        $fixture->setCountry('My Title');
        $fixture->setPhone('My Title');
        $fixture->setEmail('My Title');
        $fixture->setIs_verified('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Company');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Company();
        $fixture->setName('Value');
        $fixture->setAddress('Value');
        $fixture->setCity('Value');
        $fixture->setZip('Value');
        $fixture->setCountry('Value');
        $fixture->setPhone('Value');
        $fixture->setEmail('Value');
        $fixture->setIs_verified('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'company[name]' => 'Something New',
            'company[address]' => 'Something New',
            'company[city]' => 'Something New',
            'company[zip]' => 'Something New',
            'company[country]' => 'Something New',
            'company[phone]' => 'Something New',
            'company[email]' => 'Something New',
            'company[is_verified]' => 'Something New',
        ]);

        self::assertResponseRedirects('/company/');

        $fixture = $this->companyRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getAddress());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getZip());
        self::assertSame('Something New', $fixture[0]->getCountry());
        self::assertSame('Something New', $fixture[0]->getPhone());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getIs_verified());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Company();
        $fixture->setName('Value');
        $fixture->setAddress('Value');
        $fixture->setCity('Value');
        $fixture->setZip('Value');
        $fixture->setCountry('Value');
        $fixture->setPhone('Value');
        $fixture->setEmail('Value');
        $fixture->setIs_verified('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/company/');
        self::assertSame(0, $this->companyRepository->count([]));
    }
}
