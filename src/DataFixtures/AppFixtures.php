<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('pierre@test.com')
            ->setPassword(
                $this->hasher->hashPassword(
                    new User(),
                    'Test1234!'
                )
            )
            ->setRoles(['ROLE_ADMIN'])
            ->setEnable(true);

        $manager->persist($user);
        $manager->flush();
    }
}
