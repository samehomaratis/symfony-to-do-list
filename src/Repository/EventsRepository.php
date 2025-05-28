<?php

namespace App\Repository;

use App\Entity\Events;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,
                                private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Events::class);
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    public function updateOrCreate(array $criteria, array $data): Events
    {
        $model = $this->findOneBy($criteria);

        if (!$model) {
            $model = new Events();
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
