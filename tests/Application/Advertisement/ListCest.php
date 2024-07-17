<?php

namespace App\Tests\Application\Advertisement;

use App\Factory\AdvertisementFactory;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use Codeception\Util\HttpCode;
use Tests\Support\ApplicationTester;

class ListCest
{
    // tests
    public function displayWhenListEmpty(ApplicationTester $I): void
    {
        $I->amOnPage('/advertisement');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->see('advertisement index');

        $I->seeElement('ul', ['id' => 'liste_adv']);
        $I->dontSeeElement('li', ['id' => 'item_adv']);
    }

    public function displayWhenListHave15Childs(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        CategoryFactory::createMany(2);
        AdvertisementFactory::createMany(15, [
            'category' => CategoryFactory::random(),
            'owner' => $user,
        ]);

        $I->amOnPage('/advertisement?page=1');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeElement('ul', ['id' => 'liste_adv']);
        $I->seeNumberOfElements('#item_adv', 10);

        $I->amOnPage('/advertisement?page=2');
        $I->seeResponseCodeIs(200);

        $I->seeNumberOfElements('#item_adv', 5);
    }
}
