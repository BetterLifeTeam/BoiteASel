<?php

namespace App\Repository;

use App\Entity\DutyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DutyType|null find($id, $lockMode = null, $lockVersion = null)
 * @method DutyType|null findOneBy(array $criteria, array $orderBy = null)
 * @method DutyType[]    findAll()
 * @method DutyType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DutyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DutyType::class);
    }

    public function findValidType(){
        return $this->createQueryBuilder('duty_type')
            ->andWhere('duty_type.status = :value')
            ->setParameter('value', true )
            ->orderBy('duty_type.title', 'ASC')
            ->getQuery()
            ->execute();
    }
}
