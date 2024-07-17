<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class UserStory extends Story
{
    public function build(): void
    {
        $this->addState('admin1', UserFactory::createOne(
            [
                'email' => 'admin@example.com',
                'firstname' => 'admin',
                'lastname' => 'premier',
                'roles' => ['ROLE_ADMIN'],
            ]
        ));
        $this->addState('admin2', UserFactory::createOne(
            [
                'email' => 'admin2@example.com',
                'firstname' => 'admin',
                'lastname' => 'second',
                'roles' => ['ROLE_ADMIN'],
            ]
        ));
        $this->addState('user1', UserFactory::createOne(
            [
                'email' => 'user@example.com',
                'firstname' => 'user',
                'lastname' => 'premier',
                'roles' => ['ROLE_USER'],
            ]
        ));
        $this->addState('user2', UserFactory::createOne(
            [
                'email' => 'user2@example.com',
                'firstname' => 'user',
                'lastname' => 'second',
                'roles' => ['ROLE_USER'],
            ]
        ));

        $this->addToPool('user_random', UserFactory::createMany(10));
        $this->addToPool('unverified_users', UserFactory::createMany(4, ['isVerified' => false]));
    }
}
