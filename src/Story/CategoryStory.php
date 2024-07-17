<?php

namespace App\Story;

use App\Factory\CategoryFactory;
use Zenstruck\Foundry\Story;

final class CategoryStory extends Story
{
    public function build(): void
    {
        $array_category = file(__DIR__.'/../DataFixtures/data/category.txt');
        $this->addState('category_without_advertisement', CategoryFactory::createOne(['name' => array_shift($array_category)]));

        foreach ($array_category as $category) {
            $this->addToPool('categories', CategoryFactory::createOne(['name' => $category]));
        }
    }
}
