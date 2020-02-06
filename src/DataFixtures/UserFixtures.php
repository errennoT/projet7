<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function encodePassword($user, $plainPassword)
    {
        return $this->passwordEncoder->encodePassword($user, $plainPassword);
    }

    public function load(ObjectManager $manager)
    {
        $user0 = new User();
        $plainPassword = 'superadministrateur';
        $newPassword = $this->encodePassword($user0, $plainPassword);

        $user0->setUsername('superadministrateur')
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPassword($newPassword)
            ->setSociety($this->getReference('super_society'));

        $manager->persist($user0);

        for ($a = 1; $a <= 15; $a++) {
            $user1 = new User();
            $plainPassword = 'administrateur';
            $newPassword = $this->encodePassword($user1, $plainPassword);

            $user1->setUsername('administrateur' .$a)
                ->setRoles(['ROLE_ADMIN'])
                ->setPassword($newPassword)
                ->setSociety($this->getReference('society' . mt_rand(1, 5)));

            $manager->persist($user1);

            $user2 = new User();
            $plainPassword = 'utilisateur';
            $newPassword = $this->encodePassword($user2, $plainPassword);

            $user2->setUsername('utilisateur' .$a)
                ->setRoles(['ROLE_USER'])
                ->setPassword($newPassword)
                ->setSociety($this->getReference('society' . mt_rand(1, 5)));

            $manager->persist($user2);
            
            $manager->flush();
        }
    }

    public function getDependencies()
    {
        return array(
            SocietyFixtures::class,
        );
    }
}
