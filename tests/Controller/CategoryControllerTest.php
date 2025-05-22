<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CategoryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private string $path = '/category/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        // Clean database before each test
        foreach ($this->manager->getRepository(Category::class)->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', $this->path);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Category index'); // Ajuste selon le contenu réel
    }

    public function testNew(): void
    {
        $this->client->request('GET', $this->path . 'new');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'category[name]' => 'Test Category',
            'category[description]' => 'Test Description',
        ]);

        self::assertResponseRedirects($this->path);
        $this->client->followRedirect();

        $category = $this->manager->getRepository(Category::class)->findOneBy(['name' => 'Test Category']);
        self::assertNotNull($category);
        self::assertSame('Test Description', $category->getDescription());
    }

    public function testShow(): void
    {
        $category = new Category();
        $category->setName('Show Category');
        $category->setDescription('Show Description');
        $this->manager->persist($category);
        $this->manager->flush();

        $this->client->request('GET', $this->path . $category->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Category'); // Ajuste selon le contenu de ton template
        self::assertSelectorTextContains('body', 'Show Category');
    }

    public function testEdit(): void
    {
        $category = new Category();
        $category->setName('Old Name');
        $category->setDescription('Old Description');
        $this->manager->persist($category);
        $this->manager->flush();

        $this->client->request('GET', $this->path . $category->getId() . '/edit');

        $this->client->submitForm('Update', [
            'category[name]' => 'New Name',
            'category[description]' => 'New Description',
        ]);

        self::assertResponseRedirects($this->path);
        $this->client->followRedirect();

        $updated = $this->manager->getRepository(Category::class)->find($category->getId());
        self::assertSame('New Name', $updated->getName());
        self::assertSame('New Description', $updated->getDescription());
    }

    public function testRemove(): void
    {
        $category = new Category();
        $category->setName('Delete Me');
        $category->setDescription('To be deleted');
        $this->manager->persist($category);
        $this->manager->flush();

        // Aller sur la page où le bouton est affiché
        $this->client->request('GET', $this->path . $category->getId());

        // Récupérer le token CSRF généré par Symfony
        $csrfToken = static::getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('delete' . $category->getId())
            ->getValue();

        // Soumettre le formulaire de suppression
        $this->client->submitForm('Delete', [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects($this->path);
        self::assertNull($this->manager->getRepository(Category::class)->find($category->getId()));
    }

    private function getCsrfToken(string $id): string
    {
        return static::getContainer()->get('security.csrf.token_manager')->getToken($id)->getValue();
    }
}
