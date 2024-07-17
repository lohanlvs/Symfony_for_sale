<?php

namespace App\Tests\Application\Command;

use App\Entity\User;
use App\Factory\UserFactory;
use DateInterval;
use DateTimeImmutable;
use Tests\Support\ApplicationTester;

class PurgeRegistrationCest
{
    // tests
    public function displayOfAllUnverifiedUser(ApplicationTester $I): void
    {
        $date = new \DateTimeImmutable(); // Date et heure actuelles

        UserFactory::createSequence([
            [
                'firstname' => 'prenom1',
                'email' => 'test1@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new \DateInterval('P10D')),
            ],
            [
                'firstname' => 'prenom2',
                'email' => 'test2@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new \DateInterval('P20D')),
            ],
        ]);

        $resultCommand = $I->runSymfonyConsoleCommand('app:purge-registration');

        $I->assertStringContainsString('prenom1', $resultCommand);
        $I->assertStringContainsString('prenom2', $resultCommand);
    }

    public function displayUnverifiedUserWithDaysOption(ApplicationTester $I): void
    {
        $date = new \DateTimeImmutable(); // Date et heure actuelles

        UserFactory::createSequence([
            [
                'firstname' => 'prenom1',
                'email' => 'test1@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new \DateInterval('P10D')),
            ],
            [
                'firstname' => 'prenom2',
                'email' => 'test2@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new \DateInterval('P20D')),
            ],
        ]);

        $resultCommand = $I->runSymfonyConsoleCommand('app:purge-registration', ['--days' => '15']);

        $I->assertStringContainsString('prenom2', $resultCommand);
    }

    /*
     Ce test ne fonctionne pas à cause de la methode runSymfonyConsoleCommand, l'input n'est pas pris en compte...


    public function deleteUnverifiedUserWithoutForce(ApplicationTester $I): void
    {
        $date = new DateTimeImmutable(); // Date et heure actuelles

        UserFactory::createSequence([
            [
                'firstname' => 'prenom1',
                'email' => 'test1@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new DateInterval('P10D')),
            ],
            [
                'firstname' => 'prenom2',
                'email' => 'test2@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new DateInterval('P20D')),
            ],
            [
                'firstname' => 'prenom3',
                'email' => 'test3@test.com',
                'isVerified' => true,
                'registeredAt' => $date->sub(new DateInterval('P20D')),
            ],
        ]);

        $resultCommand = $I->runSymfonyConsoleCommand('app:purge-registration', ['--days' => '15', '--delete' => true], ['oui']);

        $I->assertStringContainsString('Opération réussie, 1 utilisateur(s) supprimé(s)', $resultCommand);

        $I->seeInRepository(User::class, [
            'email' => 'test3@test.com',
            'firstname' => 'prenom3',
            'isVerified' => true,
        ]);

        $I->seeInRepository(User::class, [
            'email' => 'test1@test.com',
            'firstname' => 'prenom1',
            'isVerified' => false,
        ]);

        $I->cantSeeInRepository(User::class, [
            'email' => 'test2@test.com',
            'firstname' => 'prenom2',
            'isVerified' => false,
        ]);
    }
    */

    public function deleteUnverifiedUserWithForce(ApplicationTester $I): void
    {
        $date = new \DateTimeImmutable(); // Date et heure actuelles

        UserFactory::createSequence([
            [
                'firstname' => 'prenom1',
                'email' => 'test1@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new \DateInterval('P10D')),
            ],
            [
                'firstname' => 'prenom2',
                'email' => 'test2@test.com',
                'isVerified' => false,
                'registeredAt' => $date->sub(new \DateInterval('P20D')),
            ],
            [
                'firstname' => 'prenom3',
                'email' => 'test3@test.com',
                'isVerified' => true,
                'registeredAt' => $date->sub(new \DateInterval('P20D')),
            ],
        ]);

        $resultCommand = $I->runSymfonyConsoleCommand('app:purge-registration', ['--days' => '15', '--delete' => true, '--force' => true]);

        $I->assertStringContainsString('Opération réussie, 1 utilisateur(s) supprimé(s)', $resultCommand);

        $I->seeInRepository(User::class, [
            'email' => 'test3@test.com',
            'firstname' => 'prenom3',
            'isVerified' => true,
        ]);

        $I->seeInRepository(User::class, [
            'email' => 'test1@test.com',
            'firstname' => 'prenom1',
            'isVerified' => false,
        ]);

        $I->cantSeeInRepository(User::class, [
            'email' => 'test2@test.com',
            'firstname' => 'prenom2',
            'isVerified' => false,
        ]);
    }
}
