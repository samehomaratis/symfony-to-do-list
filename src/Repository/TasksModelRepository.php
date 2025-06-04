<?php

namespace App\Repository;

use App\Entity\TasksModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TasksModel>
 */
class TasksModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry                $registry,
                                private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, TasksModel::class);
    }

    //    /**
    //     * @return TasksModel[] Returns an array of TasksModel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TasksModel
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function updateOrCreate(array $criteria, array $data = []): TasksModel
    {
        $model = $this->findOneBy($criteria);

        if (!$model) {
            $model = new TasksModel();
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
