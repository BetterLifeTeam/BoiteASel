<?php

namespace App\Repository;

use DateTime;
use App\Entity\Duty;
use App\Entity\Member;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function getHelpers($limit = null)
    {

        $sql = 'SELECT m.firstname, m.name, m.id, 
        (SELECT SUM(d.price) FROM duty as d WHERE d.offerer_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as higher, 
        (SELECT MAX(du.done_at) FROM duty as du WHERE du.offerer_id=m.id AND du.status="finished") as last_duty
        FROM member as m
        ORDER BY higher DESC';

        if ($limit != null) {
            $sql .= " LIMIT ".$limit;
        }

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
        // return $sql;

    }

    public function getAskers()
    {

        $sql = 'SELECT m.firstname, m.name, m.id, 
        (SELECT SUM(d.price) FROM duty as d WHERE asker_id=m.id AND d.status = "finished" AND d.done_at >= DATE_SUB(curdate(), INTERVAL 2 WEEK)) as `lower`, 
        (SELECT MAX(du.done_at) FROM duty as du WHERE du.asker_id=m.id) as last_duty
        FROM member as m  
        ORDER BY `lower` DESC
        LIMIT 5';

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }

    public function getActualites($limit = null)
    {

        $sql = 'SELECT d.id, askM.firstname as askerFirstName, askM.name as askerName, offM.firstname as offererFirstName, offM.name as offererName, dt.title as type, d.created_at, d.done_at, d.price
        FROM duty as d
        LEFT JOIN member as askM on d.asker_id=askM.id
        LEFT JOIN member as offM on d.offerer_id=offM.id
        LEFT JOIN duty_type as dt on d.duty_type_id=dt.id
        WHERE d.status = "finished"
        ORDER BY d.done_at DESC';

        if ($limit != null) {
            $sql .= " LIMIT ".$limit;
        }
        

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }

    public function getTypeActivites()
    {

        $sql = 'SELECT dt.id, dt.title, dt.hourly_price,
        (select count(d.id) from duty as d where d.duty_type_id = dt.id and d.status="finished" and d.done_at >= DATE_SUB(curdate(), INTERVAL 1 MONTH)) as howMany,
        (select sum(du.price) from duty as du where du.duty_type_id = dt.id and du.status="finished" and du.done_at >= DATE_SUB(curdate(), INTERVAL 1 MONTH)) as saltAmount
        FROM duty_type as dt
        ORDER BY howMany DESC';

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }

    public function getVolumesEchanges()
    {
        date_default_timezone_set("Europe/Paris");

        $startDate = date("Y-m-d H:i:s", strtotime("-2 months")); 
        $endDate = date("Y-m-d H:i:s", strtotime("1 weeks", strtotime($startDate)));
        
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        $interval = $startDateTime->diff(new DateTime());

        $toReturn = [];
        
        while ($interval->days > 4) {

            $sql = 'SELECT
            (select sum(d1.price) from duty as d1 where d1.status = "finished" and d1.done_at between "'.$startDate.'" AND "'.$endDate.'") as saltAmount,
            (select count(d2.id) from duty as d2 where d2.status = "finished" and d2.done_at between "'.$startDate.'" AND "'.$endDate.'") as dutiesAmount
            FROM duty as d
            LIMIT 1';
    
            $em = $this->getEntityManager();
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();

            $toReturn[] = array(
                "weekStart" => date("d/m/Y", strtotime($startDate)),
                "weekEnd" => date("d/m/Y", strtotime($endDate)),
                "saltAmount" => $result[0]["saltAmount"],
                "dutiesAmount" => $result[0]["dutiesAmount"],
            );

            $startDate = date("Y-m-d H:i:s", strtotime("1 weeks", strtotime($startDate))); 
            $endDate = date("Y-m-d H:i:s", strtotime("1 weeks", strtotime($startDate)));
            $startDateTime = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);
            $interval = $startDateTime->diff(new DateTime());
        }

        $toReturn[array_key_last($toReturn)]["weekEnd"] = date("d/m/Y");

        return $toReturn;

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
