<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($a = 1; $a <= 30; $a++) {

            $product = new Product();
            $product->setModel('product' .$a);
            $product->setSizeScreen(mt_rand(4, 8). "''");
            $product->setColor('black');
            $product->setPrice(mt_rand(100, 800));
            $product->setCreatedAt(new \DateTime());

            $manager->persist($product);	
            $manager->flush();
        }
    }
}