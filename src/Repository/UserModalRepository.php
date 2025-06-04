<?php

namespace App\Repository;

use App\Entity\UserModal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserModal>
 */
class UserModalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,
                                private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, UserModal::class);
    }

    //    /**
    //     * @return UserModal[] Returns an array of UserModal objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserModal
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function updateOrCreate(array $criteria, array $data): UserModal
    {
        $model = $this->findOneBy($criteria);

        if (!$model) {
            $model = new UserModal();
            foreach ($criteria as $field => $value) {
                $field_parts = explode('_', $field);
                $setter = 'set';
                foreach ($field_parts as $part) {
                    $setter .= ucfirst($part);
                }
                if (method_exists($model, $setter)) {
                    $model->$setter($value);
                }
            }
        }

        foreach ($data as $field => $value) {
            $field_parts = explode('_', $field);
            $setter = 'set';
            foreach ($field_parts as $part) {
                $setter .= ucfirst($part);
            }
            if (method_exists($model, $setter)) {
                $model->$setter($value);
            }
        }

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return $model;
    }

}
