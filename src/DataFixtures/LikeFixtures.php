<?php

namespace App\DataFixtures;

use App\Factory\LikeFactory;
use App\Repository\AdvertisementRepository;
use App\Story\UserStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Factory;

class LikeFixtures extends Fixture implements DependentFixtureInterface
{
    private AdvertisementRepository $advertisementRepository;

    public function __construct(AdvertisementRepository $advertisementRepository)
    {
        $this->advertisementRepository = $advertisementRepository;
    }

    public function load(ObjectManager $manager): void
    {
        Factory::delayFlush(function () {
            $advertisements = $this->advertisementRepository->findByNotOwnedUser(UserStory::get('user1')->object());

            foreach (array_rand($advertisements, rand(50, 100)) as $id) {
                LikeFactory::createOne([
                    'owner' => UserStory::get('user1'),
                    'advertisement' => $advertisements[$id],
                ]);
            }

            foreach (UserStory::getPool('user_random') as $user) {
                $advertisements = $this->advertisementRepository->findByNotOwnedUser($user->object());
                foreach (array_rand($advertisements, rand(50, 100)) as $id) {
                    LikeFactory::createOne([
                        'owner' => $user,
                        'advertisement' => $advertisements[$id],
                    ]);
                }
            }
        });
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
            AdvertisementFixtures::class,
        ];
    }
}
