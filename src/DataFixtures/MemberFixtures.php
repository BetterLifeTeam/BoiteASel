<?php

namespace App\DataFixtures;

use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MemberFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $faker = Faker\Factory::create('fr_FR');
        // $product = new Product();
        // $manager->persist($product);

        for ($i=1; $i < 20; $i++) { 
            $member = new Member();
            // $member->setName($faker->)
        }

        $manager->flush();
    }
}
