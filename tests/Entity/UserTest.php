<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    // Teste si un utilisateur créé génère automatiquement un jeton API
    public function testTheAutomaticApiTokenSettingWhenAnUserIsCreated(): void
    {
        $user = new User();
        // Vérifie que l'API token n'est pas null
        $this->assertNotNull($user->getApiToken());
    }

    // Teste si un utilisateur a au moins un rôle ROLE_USER
    public function testThanAnUserHasAtLeastOneRoleUser(): void
    {
        $user = new User();
        // Vérifie que l'utilisateur contient le rôle ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    // Teste si une exception est lancée lorsqu'un type incorrect est passé
    public function testAnException(): void
    {
        $this->expectException(\TypeError::class);

        $user = new User();
        // Vérifie que setFirstName génère une erreur avec un type incorrect
        $user->setFirstName([10]);  // Passe un tableau au lieu d'une chaîne de caractères
    }

    // Fournisseur de noms pour tester setFirstName
    public function provideFirstName(): \Generator
    {
        yield ['Thomas'];
        yield ['Eric'];
        yield ['Marie'];
    }

    /** @dataProvider provideFirstName */
    // Teste si le setter pour firstName fonctionne correctement
    public function testFirstNameSetter(string $name): void
    {
        $user = new User();
        $user->setFirstName($name);

        // Vérifie que le prénom a bien été défini
        $this->assertSame($name, $user->getFirstName());
    }
}
