<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findNoReadNotification($user){
        return $this->createQueryBuilder('notification')
            ->andWhere('notification.member = :value AND notification.isRead = :status')
            ->setParameters(array('value' => $user, 'status' => 'false'))
            ->orderBy('notification.createdAt', 'DESC')
            ->getQuery()
            ->execute();
    }
}
