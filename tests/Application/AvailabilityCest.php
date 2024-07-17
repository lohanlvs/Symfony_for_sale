<?php

namespace App\Tests\Application;

use App\Factory\AdvertisementFactory;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Group;
use Codeception\Example;
use Codeception\Util\HttpCode;
use Tests\Support\ApplicationTester;

class AvailabilityCest
{
    protected function urlProvider(): array
    {
        return [
            ['url' => '/register'],
            ['url' => '/verify/email'],
            ['url' => '/verify'],
            ['url' => '/advertisement'],
            ['url' => '/advertisement/1'],
            ['url' => '/advertisement/1/edit'],
            ['url' => '/advertisement/new'],
            ['url' => '/category'],
            ['url' => '/category/1'],
            ['url' => '/login'],
            ['url' => '/logout'],
            ['url' => '/user/adv/1'],
            ['url' => '/advertisement/liked'],
        ];
    }

    // tests
    #[DataProvider('urlProvider')]
    #[Group('available')]
    public function pageIsAvailable(ApplicationTester $I, Example $url): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne();
        AdvertisementFactory::createMany(10, [
            'category' => $category,
            'owner' => $user,
        ]);

        $I->amLoggedInAs($user->object());

        $I->amOnPage($url['url']);
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
