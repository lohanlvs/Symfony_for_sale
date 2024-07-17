<?php

namespace App\DataFixtures;

use App\Entity\Advertisement;
use App\Factory\AdvertisementFactory;
use App\Story\CategoryStory;
use App\Story\UserStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Factory;

class AdvertisementFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        Factory::delayFlush(function () {
            AdvertisementFactory::createMany(500, function () {
                return [
                    'category' => CategoryStory::getRandom('categories'),
                    'owner' => UserStory::getRandom('user_random'),
                ];
            });
            AdvertisementFactory::createMany(20, function () {
                return [
                    'category' => CategoryStory::getRandom('categories'),
                    'owner' => UserStory::get('user1'),
                    'currentState' => [
                        Advertisement::STATE_DRAFT,
                        Advertisement::STATE_PUBLISHED,
                        Advertisement::STATE_CLOSED,
                        Advertisement::STATE_ARCHIVED,
                    ][rand(0, 3)],
                ];
            });
        });
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }
}
