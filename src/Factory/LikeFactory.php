<?php

namespace App\Factory;

use App\Entity\Like;
use App\Repository\LikeRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Like>
 *
 * @method        Like|Proxy                     create(array|callable $attributes = [])
 * @method static Like|Proxy                     createOne(array $attributes = [])
 * @method static Like|Proxy                     find(object|array|mixed $criteria)
 * @method static Like|Proxy                     findOrCreate(array $attributes)
 * @method static Like|Proxy                     first(string $sortedField = 'id')
 * @method static Like|Proxy                     last(string $sortedField = 'id')
 * @method static Like|Proxy                     random(array $attributes = [])
 * @method static Like|Proxy                     randomOrCreate(array $attributes = [])
 * @method static LikeRepository|RepositoryProxy repository()
 * @method static Like[]|Proxy[]                 all()
 * @method static Like[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Like[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Like[]|Proxy[]                 findBy(array $attributes)
 * @method static Like[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Like[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class LikeFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'advertisement' => AdvertisementFactory::new(),
            'owner' => UserFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Like $like): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Like::class;
    }
}
