<?php

namespace App\Repository;

use App\Entity\Duty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Duty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Duty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Duty[]    findAll()
 * @method Duty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DutyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Duty::class);
    }

    // /**
    //  * @return Duty[] Returns an array of Duty objects
    //  */

    public function findByKey($params, $filter){
        return $this->createQueryBuilder('duty')
            ->andWhere('duty.title LIKE :value OR duty.description LIKE :value OR duty.place LIKE :value')
            ->setParameter('value', '%'.$params.'%')
            ->orderBy('duty.createdAt', $filter)
            ->getQuery()
            ->execute();
    }

    public function findByKeyAndType($params, $filter, $type = null){
        return $this->createQueryBuilder('duty')
            ->andWhere('duty.title LIKE :value OR duty.description LIKE :value OR duty.place LIKE :value')
            ->setParameter('value', '%'.$params.'%')
            ->andWhere('duty.dutyType = :type')
            ->setParameter('type', $type)
            ->orderBy('duty.createdAt', $filter)
            ->getQuery()
            ->execute();
    }

}
