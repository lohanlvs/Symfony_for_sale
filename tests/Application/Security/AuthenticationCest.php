<?php

namespace App\Tests\Application\Security;

use App\Factory\UserFactory;
use Codeception\Util\HttpCode;
use Tests\Support\ApplicationTester;

class AuthenticationCest
{
    // tests
    public function userCanConnect(ApplicationTester $I): void
    {
        UserFactory::createOne([
            'email' => 'coucou@example.com',
            'password' => 'test',
        ]);

        $I->amOnPage('/login');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField(['id' => 'inputEmail'], 'coucou@example.com');
        $I->fillField(['id' => 'inputPassword'], 'test');
        $I->click('Connexion');

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amOnPage('/advertisement');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeElement('a', ['id' => 'lougout_link']);

        $I->seeAuthentication();
    }

    public function userCanDisconnect(ApplicationTester $I): void
    {
        $user = UserFactory::createOne([
            'email' => 'coucou@example.com',
            'password' => 'test',
        ]);

        $I->amLoggedInAs($user->object());
        $I->seeAuthentication();

        $I->amOnPage('/advertisement');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeElement('a', ['id' => 'lougout_link']);

        $I->click(['id' => 'lougout_link']);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->cantSeeAuthentication();
    }

    public function userCantConnectWithInvalidCredential(ApplicationTester $I): void
    {
        UserFactory::createOne([
            'email' => 'coucou@example.com',
            'password' => 'test',
        ]);

        $I->amOnPage('/login');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField(['id' => 'inputEmail'], 'coucou@examplez.com');
        $I->fillField(['id' => 'inputPassword'], 'test');
        $I->click('Connexion');

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->cantSeeAuthentication();
    }
}
