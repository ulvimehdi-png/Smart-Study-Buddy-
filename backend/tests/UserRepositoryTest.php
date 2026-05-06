<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends KernelTestCase
{
    public function testUpgradePassword(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $repo = $container->get(UserRepository::class);
        $em = $container->get(EntityManagerInterface::class);

        $user = new User();
        $user->setUsername('repo_test_' . uniqid());
        $user->setPassword('old_hash');
        $em->persist($user);
        $em->flush();

        $repo->upgradePassword($user, 'new_hash');
        $this->assertSame('new_hash', $user->getPassword());

        $this->expectException(UnsupportedUserException::class);
        $mockUser = $this->createMock(PasswordAuthenticatedUserInterface::class);
        $repo->upgradePassword($mockUser, 'hash');
    }
}