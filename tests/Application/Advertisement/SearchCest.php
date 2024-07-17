<?php

namespace App\Tests\Application\Advertisement;

use App\Factory\AdvertisementFactory;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use Codeception\Util\HttpCode;
use Tests\Support\ApplicationTester;

class SearchCest
{
    // tests
    public function searchFormWork(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne();
        AdvertisementFactory::createSequence([
            [
                'title' => 'Coucou',
                'description' => 'zzzzzzzzzzzzzzzzzzzzzzz',
                'category' => $category,
                'owner' => $user,
            ],
            [
                'title' => 'zzzzzzzzzzzzzzzzzzzzzzz',
                'description' => 'zzzCoucouzz',
                'category' => $category,
                'owner' => $user,
            ],
            [
                'title' => 'yyyyy',
                'description' => 'yyyyyyyyyyyyyyy',
                'category' => $category,
                'owner' => $user,
            ],
        ]);

        $I->amOnPage('/advertisement?page=1');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeElement('ul', ['id' => 'liste_adv']);
        $I->seeNumberOfElements('#item_adv', 3);

        $I->submitForm('#search_form', ['search' => 'coucou']);
        $I->seeResponseCodeIs(200);

        $I->seeElement('ul', ['id' => 'liste_adv']);
        $I->seeNumberOfElements('#item_adv', 2);
    }
}
