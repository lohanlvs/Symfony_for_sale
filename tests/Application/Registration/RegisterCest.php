<?php

namespace App\Tests\Application\Registration;

use App\Entity\User;
use App\Factory\UserFactory;
use Tests\Support\ApplicationTester;

class RegisterCest
{
    // tests
    public function rejectIncorrectPasswordConfirmationDuringRegistration(ApplicationTester $I): void
    {
        $I->amOnPage('/register');
        $I->seeResponseCodeIsSuccessful();

        $I->fillField(['id' => 'registration_form_email'], 'test@gmail.com');
        $I->fillField(['id' => 'registration_form_password_first'], 'COUcou123$');
        $I->fillField(['id' => 'registration_form_password_second'], 'couCOU123$');
        $I->fillField(['id' => 'registration_form_firstname'], 'Jean-Eude');
        $I->fillField(['id' => 'registration_form_lastname'], 'Delaporte');
        $I->click(['id' => 'submit_button_register']);

        $I->see('Le mot de passe doit correspondre.');

        $I->cantSeeInRepository(User::class, [
            'email' => 'test@gmail.com',
            'firstname' => 'Jean-Eude',
            'lastname' => 'Delaporte',
        ]);
    }

    public function rejectInvalidPasswordComplexityDuringRegistration(ApplicationTester $I): void
    {
        $I->amOnPage('/register');
        $I->seeResponseCodeIsSuccessful();

        $I->fillField(['id' => 'registration_form_email'], 'test@gmail.com');
        $I->fillField(['id' => 'registration_form_password_first'], 'coucou');
        $I->fillField(['id' => 'registration_form_password_second'], 'coucou');
        $I->fillField(['id' => 'registration_form_firstname'], 'Jean-Eude');
        $I->fillField(['id' => 'registration_form_lastname'], 'Delaporte');
        $I->click(['id' => 'submit_button_register']);

        $I->seeElement('div', ['class' => 'invalid-feedback d-block']);

        $I->cantSeeInRepository(User::class, [
            'email' => 'test@gmail.com',
            'firstname' => 'Jean-Eude',
            'lastname' => 'Delaporte',
        ]);
    }

    public function redirectUnconfirmedEmailToConfirmationPage(ApplicationTester $I): void
    {
        $user = UserFactory::createOne(['is_verified' => false]);
        $I->amLoggedInAs($user->object());

        $urlList = [
            '/advertisement',
            '/advertisement/1',
            '/advertisement/new',
            '/category',
            '/category/1',
        ];

        foreach ($urlList as $url) {
            $I->amOnPage($url);
            $I->seeResponseCodeIsSuccessful();
            $I->seeCurrentRouteIs('app_ask_confirm');
        }
    }

    public function fillRegistrationFormReceiveConfirmationEmailAndValidateEmailAddress(ApplicationTester $I): void
    {
        $I->amOnPage('/register');
        $I->seeResponseCodeIsSuccessful();

        $I->stopFollowingRedirects();

        $I->fillField(['id' => 'registration_form_email'], 'test@gmail.com');
        $I->fillField(['id' => 'registration_form_password_first'], 'COUcou123$');
        $I->fillField(['id' => 'registration_form_password_second'], 'COUcou123$');
        $I->fillField(['id' => 'registration_form_firstname'], 'Jean-Eude');
        $I->fillField(['id' => 'registration_form_lastname'], 'Delaporte');
        $I->click(['id' => 'submit_button_register']);

        $I->seeInRepository(User::class, [
            'email' => 'test@gmail.com',
            'firstname' => 'Jean-Eude',
            'lastname' => 'Delaporte',
            'isVerified' => false,
        ]);

        $I->seeEmailIsSent(1);

        $email = $I->grabLastSentEmail();
        $body = $email->getHtmlBody();

        $regex = '/<a\s+href="([^"]*)"/i';
        preg_match($regex, $body, $matches);

        $link = $matches[1];

        $I->amOnPage($link);

        $I->seeInRepository(User::class, [
            'email' => 'test@gmail.com',
            'firstname' => 'Jean-Eude',
            'lastname' => 'Delaporte',
            'isVerified' => true,
        ]);
    }
}
