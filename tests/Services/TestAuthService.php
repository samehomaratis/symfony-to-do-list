<?php

namespace App\Tests\Services;

use App\Entity\UserModal;
use App\Repository\UserModalRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestAuthService extends WebTestCase
{
    public function createUser()
    {
        $container = static::getContainer();

        $repo = $container->get(UserModalRepository::class);

        $testUser = $repo->findOneBy(['email' => 'test@example.com']);
        if (!$testUser) {
            $testUser = new UserModal();
            $testUser->setEmail('test@example.com');
            $testUser->setName('Test User');
            $testUser->setPassword(
                self::getContainer()
                    ->get(UserPasswordHasherInterface::class)
                    ->hashPassword($testUser, 'testpass')
            );

            // Persist to the test database
            $em = self::getContainer()->get('doctrine')->getManager();
            $em->persist($testUser);
            $em->flush();
        }

        return $testUser;
    }

}