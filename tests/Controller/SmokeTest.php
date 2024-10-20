<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @group Functional */
final class SmokeTest extends WebTestCase
{
    // Fonction pour créer un utilisateur de test pendant le test
    public function createTestUser(EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): void
    {
        $user = new User();
        $user->setEmail('jane.doe@zooarcadia.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        // Persiste l'utilisateur dans la base de données
        $manager->persist($user);
        $manager->flush();
    }

    public function testApiDocUrlIsSuccessful(): void
    {
        // Créer un client et tester l'URL '/api/doc'
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/api/doc');

        // Vérifier si la réponse est réussie
        self::assertResponseIsSuccessful();
    }

    public function testApiAccountUrlIsSecure(): void
    {
        // Vérifier la sécurité de '/api/account/me'
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/api/account/me');

        // Vérifier si la réponse est 401 (accès non autorisé)
        self::assertResponseStatusCodeSame(401);
    }

    public function testLoginRouteCanConnectAValidUser(): void
    {
        // Créer un client
        $client = self::createClient();
        $client->followRedirects(false);

        // Créer un utilisateur et l'ajouter à la base de données de test
        $this->createTestUser($client->getContainer()->get('doctrine.orm.entity_manager'), $client->getContainer()->get('security.user_password_hasher'));

        // Essayer de connecter cet utilisateur
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'jane.doe@zooarcadia.com',
            'password' => 'password123',
        ], JSON_THROW_ON_ERROR));

        // Vérifier le code de statut et le contenu de la réponse
        $statusCode = $client->getResponse()->getStatusCode();
        $content = $client->getResponse()->getContent();

        // Vérifier que le code de statut est 200
        $this->assertEquals(200, $statusCode);

        // Vérifier que la réponse contient 'user'
        $this->assertStringContainsString('user', $content);

        // Vérifier que la réponse contient 'apiToken'
        $this->assertStringContainsString('apiToken', $content);

        // Vérifier que la réponse contient 'roles'
        $this->assertStringContainsString('roles', $content);

        // Vérifier si la réponse est réussie
        self::assertResponseIsSuccessful();
    }
}
