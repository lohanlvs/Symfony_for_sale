<?php

namespace App\Tests\Application\Advertisement;

use App\Entity\Advertisement;
use App\Factory\AdvertisementFactory;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use Codeception\Util\HttpCode;
use Tests\Support\ApplicationTester;

class CRUDCest
{
    public function formAdvertisementWorkForConnectedUser(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne([
            'name' => 'coucou',
        ]);

        $I->amLoggedInAs($user->object());
        $I->canSeeAuthentication();

        $I->amOnPage('/advertisement/new');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField(['id' => 'advertisement_title'], 'Hello World!');
        $I->fillField(['id' => 'advertisement_description'], 'Hello World!zzzzzzzzzzz');
        $I->fillField(['id' => 'advertisement_price'], 1000);
        $I->fillField(['id' => 'advertisement_location'], 'Hello World!');
        $I->selectOption(['id' => 'advertisement_category'], 'coucou');
        $I->click('Save');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeInRepository(Advertisement::class, [
            'title' => 'Hello World!',
            'description' => 'Hello World!zzzzzzzzzzz',
            'price' => 1000,
            'location' => 'Hello World!',
            'category' => $category->getId(),
        ]);
    }

    public function AdvertisementHasGoodOwner(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne([
            'name' => 'coucou',
        ]);

        $I->amLoggedInAs($user->object());
        $I->seeAuthentication();

        $I->amOnPage('/advertisement/new');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField(['id' => 'advertisement_title'], 'Hello World!');
        $I->fillField(['id' => 'advertisement_description'], 'Hello World!zzzzzzzzzzz');
        $I->fillField(['id' => 'advertisement_price'], 1000);
        $I->fillField(['id' => 'advertisement_location'], 'Hello World!');
        $I->selectOption(['id' => 'advertisement_category'], 'coucou');
        $I->click('Save');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeInRepository(Advertisement::class, [
            'title' => 'Hello World!',
            'description' => 'Hello World!zzzzzzzzzzz',
            'price' => 1000,
            'location' => 'Hello World!',
            'owner' => $user->getId(),
        ]);
    }

    public function formAdvertisementNotWorkForConnectedUser(ApplicationTester $I): void
    {
        CategoryFactory::createMany(10);

        $I->cantSeeAuthentication();

        $I->amOnPage('/advertisement/new');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeCurrentRouteIs('app_login');
    }

    public function advertisementHaveRightData(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne();
        $adv = AdvertisementFactory::createOne([
            'category' => $category,
            'description' => "coucou c'est un test tu as vu ?",
            'location' => 'Tataouine les bains',
            'price' => 9999,
            'title' => 'TEST',
            'owner' => $user,
            'currentState' => Advertisement::STATE_PUBLISHED,
        ]);

        $I->amOnPage('/advertisement/1');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeElement('p', ['id' => 'cat_adv_show']);
        $I->seeElement('p', ['id' => 'date_adv_show']);
        $I->seeElement('h1', ['id' => 'title_adv_show']);
        $I->seeElement('p', ['id' => 'price_adv_show']);
        $I->seeElement('p', ['id' => 'location_adv_show']);

        $I->see("Description: coucou c'est un test tu as vu ?");
        $I->see('Tataouine les bains');
        $I->see('Price: 9999€');
        $I->see('TEST');
        $I->see('Location: Tataouine les bains');
        $I->see('Date de création: '.$adv->getCreatedAt()->format('d/m/y'));

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'description' => "coucou c'est un test tu as vu ?",
            'location' => 'Tataouine les bains',
            'price' => 9999,
            'title' => 'TEST',
            'owner' => $user->object(),
        ]);
    }

    public function editOfAdvertisementWork(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne();
        $adv = AdvertisementFactory::createOne([
            'category' => $category,
            'description' => "coucou c'est un test tu as vu ?",
            'location' => 'Tataouine les bains',
            'price' => 9999,
            'title' => 'TEST',
            'owner' => $user,
        ]);

        $I->amLoggedInAs($user->object());

        $I->amOnPage('/advertisement/1');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->see("Description: coucou c'est un test tu as vu ?");
        $I->see('Tataouine les bains');
        $I->see('Price: 9999€');
        $I->see('TEST');
        $I->see('Location: Tataouine les bains');
        $I->see('Date de création: '.$adv->getCreatedAt()->format('d/m/y'));

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'description' => "coucou c'est un test tu as vu ?",
            'location' => 'Tataouine les bains',
            'price' => 9999,
            'title' => 'TEST',
            'owner' => $user->object(),
        ]);

        $I->amOnPage('/advertisement/1/edit');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField(['id' => 'advertisement_title'], 'ALED OSCOUR');
        $I->click('Save');

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amOnPage('/advertisement/1');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->see('ALED OSCOUR');

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'description' => "coucou c'est un test tu as vu ?",
            'location' => 'Tataouine les bains',
            'price' => 9999,
            'title' => 'ALED OSCOUR',
            'owner' => $user->object(),
        ]);
    }

    public function deleteDraftAdvertisementWorkWithAClickOnButton(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne();
        AdvertisementFactory::createOne([
            'category' => $category,
            'owner' => $user,
            'currentState' => Advertisement::STATE_DRAFT,
        ]);

        $I->amLoggedInAs($user->object());

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'owner' => $user->object(),
        ]);

        $I->amOnPage('/advertisement/1');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Delete');

        $I->cantSeeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'owner' => $user->object(),
        ]);
    }

    public function deleteButtonNotHereForPublishingAdvertisement(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne();
        AdvertisementFactory::createOne([
            'category' => $category,
            'owner' => $user,
            'currentState' => Advertisement::STATE_PUBLISHED,
        ]);

        $I->amLoggedInAs($user->object());

        $I->amOnPage('/advertisement/1');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->cantSeeElement('button', ['id' => 'button_delete']);
    }

    public function deleteDraftAdvertisementWithoutButtonDontWork(ApplicationTester $I): void
    {
        $user = UserFactory::createOne();
        $category = CategoryFactory::createOne();
        AdvertisementFactory::createOne([
            'category' => $category,
            'owner' => $user,
            'currentState' => Advertisement::STATE_DRAFT,
        ]);

        $I->amLoggedInAs($user->object());

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'owner' => $user->object(),
        ]);

        $I->amOnPage('/advertisement/1/delete');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'owner' => $user->object(),
        ]);
    }

    public function deleteAdvertisementOfOtherUserDontWork(ApplicationTester $I): void
    {
        $user = UserFactory::createMany(2);
        $category = CategoryFactory::createOne();
        AdvertisementFactory::createOne([
            'category' => $category->object(),
            'owner' => $user[1]->object(),
            'currentState' => Advertisement::STATE_DRAFT,
        ]);

        $I->amLoggedInAs($user[0]->object());

        $I->amOnPage('/advertisement/1');

        $I->cantSeeElement('button', ['id' => 'button_delete']);

        $I->amOnPage('/advertisement/1/delete');
        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentRouteIs('app_advertisement_index');

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'owner' => $user[1]->object(),
        ]);
    }

    public function editAdvertisementOfOtherUserDontWork(ApplicationTester $I): void
    {
        $user = UserFactory::createMany(2);
        $category = CategoryFactory::createOne();
        AdvertisementFactory::createOne([
            'category' => $category->object(),
            'owner' => $user[1]->object(),
        ]);

        $I->amLoggedInAs($user[0]->object());

        $I->amOnPage('/advertisement/1');

        $I->cantSeeElement('button', ['id' => 'button_edit']);

        $I->amOnPage('/advertisement/1/edit');
        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentRouteIs('app_advertisement_index');

        $I->seeInRepository(Advertisement::class, [
            'category' => $category->object(),
            'owner' => $user[1]->object(),
        ]);
    }
}
