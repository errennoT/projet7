<?php

namespace App\DataFixtures;

use App\Entity\Society;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SocietyFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $superSociety = new Society();
        $superSociety->setName('super_society');

        $this->addReference('super_society', $superSociety);

        for ($a = 1; $a <= 5; $a++) {

            $society = new Society();
            $society->setName('society' .$a);

            $this->addReference('society'.$a, $society);

            $manager->persist($society);	
            $manager->flush();
        }
    }
}