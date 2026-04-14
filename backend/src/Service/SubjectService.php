<?php

namespace App\Service;

use App\Entity\Subject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SubjectService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getOrCreateSubject(User $user, ?string $name): ?Subject
    {
        /*
         * Service currently assumes subject names are unique per user, while DB
         * only guarantees a unique code, not name
         *      ==> dedup in service only or add unique constraint later on (user_id, name)
         */

        $name = null !== $name ? trim($name) : null;

        if (null === $name || '' === $name) {
            return null;
        }

        $subject = $this->em->getRepository(Subject::class)
            ->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('LOWER(s.name) = LOWER(:name)')
            ->setParameter('user', $user)
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($subject instanceof Subject) {
            if ($subject->isDeleted()){
                $subject->restore();
            }

            return $subject;
        }

        $subject = new Subject();
        $subject->setUser($user);
        $subject->setName($name);

        $this->em->persist($subject);

        return $subject;
    }
}
