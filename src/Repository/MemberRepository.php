<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Duty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function countAdminAndSupAdmin()
    {

        // $entityManager = $this->getEntityManager();

        $qb = $this->createQueryBuilder('m')
            ->select('count(m)')
            ->where('m.roles LIKE :admin')
            ->orWhere('m.roles LIKE :supadmin')
            ->setParameters(array('admin' => '%ROLE_ADMIN%', 'supadmin' => '%ROLE_SUPER_ADMIN%'));


        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getHelpers()
    {
        /*
        SELECT m.name, m.firstname, m.id, 
        (SELECT SUM(d.price) FROM duty as d WHERE offerer_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as higher, 
        (SELECT MAX(du.done_at) FROM duty as du WHERE du.offerer_id=m.id) as last_duty
        FROM member as m
        ORDER BY higher DESC
        LIMIT 5
        */



        $rsm = new ResultSetMapping();

        // $query = $this->getEntityManager()->createNativeQuery('SELECT concat(m.name, " ", m.firstname) as memberName, m.id, 
        // (SELECT SUM(d.price) FROM duty as d WHERE offerer_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as higher, 
        // (SELECT MAX(du.done_at) FROM duty as du WHERE du.offerer_id=m.id) as last_duty
        // FROM member as m
        // ORDER BY higher DESC LIMIT 5', $rsm);
        // $query = $this->getEntityManager()->createNativeQuery('SELECT * FROM member', $rsm);

        $rsm->addEntityResult("\App\Entity\Member", "m")
            ->addEntityResult("\App\Entity\Duty", "d")
            ->addEntityResult("\App\Entity\Duty", "du")
            ->addFieldResult("m", 'concat(m.name, " ", m.firstname)', "memberName")
            ->addFieldResult("m", 'id', "id")
            ->addScalarResult('(SELECT SUM(d.price) FROM duty as d WHERE offerer_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK))', 'higher')
            ->addScalarResult('(SELECT MAX(du.done_at) FROM duty as du WHERE du.offerer_id=m.id)', 'lastDuty');

        $sql = 'SELECT concat(m.name, " ", m.firstname) as memberName, m.id, 
        (SELECT SUM(d.price) FROM duty as d WHERE offerer_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as higher, 
        (SELECT MAX(du.done_at) FROM duty as du WHERE du.offerer_id=m.id) as last_duty
        FROM member as m
        ORDER BY higher DESC 
        LIMIT 5';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        return $query->getResult();

        // $sql = 'SELECT concat(m.name, " ", m.firstname) as memberName, m.id, 
        // (SELECT SUM(d.price) FROM duty as d WHERE offerer_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as higher, 
        // (SELECT MAX(du.done_at) FROM duty as du WHERE du.offerer_id=m.id) as last_duty
        // FROM member as m
        // ORDER BY higher DESC';

        // if ($limit != null) {
        //     $sql .= "LIMIT " . $limit;
        // }
        
        // $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // $rsm->add

        return $query->getScalarResult();
    }

    // /**
    //  * @return Member[] Returns an array of Member objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Member
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
