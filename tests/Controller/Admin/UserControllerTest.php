<?php

// tests/Controller/UserControllerTest.php
namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->findOneByUsername('jane_admin');
        $this->client->loginUser($user);
    }
    public function testIndex()
    {
        $crawler = $this->client->request('GET', 'fr/admin/users/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Liste des membres');
    }

    public function testCreateUser()
    {
        $crawler = $this->client->request('GET', 'fr/admin/users/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Ajouter un nouvel utilisateur');


        $form = $crawler->selectButton('Créer un nouvel utilisateur.')->form([
            'user[username]' => 'valentin_michel',
            'user[fullName]' => 'Valentin Michel',
            'user[email]' => 'valentin_michel@outlook.fr',
            'user[password]' => 'password123',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'Nouvel utilisateur créé avec succès.');
    }

}
