<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $electricity = new Category();
        $electricity->setName('Électricité');
        $manager->persist($electricity);

        $manager->flush();
    }
}
