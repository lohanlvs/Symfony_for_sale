<?php

namespace App\Tests\Application;

use Codeception\Util\HttpCode;
use Tests\Support\ApplicationTester;

class HomeCest
{
    // tests
    public function redirectHomeToAdvertisementIndex(ApplicationTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeCurrentUrlEquals('/advertisement');
    }
}
