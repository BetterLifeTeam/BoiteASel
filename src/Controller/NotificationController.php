<?php

namespace App\Controller;

use App\Entity\Duty;
use App\Entity\Member;
use App\Entity\Notification;
use App\Repository\DutyRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    /**
     * @Route("/notification", name="notification")
     */
    public function userNotification(NotificationRepository $notificationRepository)
    {
        $user = $this->getUser();
        $notifications = $notificationRepository->findNoReadNotification($user);
        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * @Route("/notification/read/{id}", name="notification_reading")
    */
    public function readNotification($id, NotificationRepository $notificationRepository)
    {
        $selectedNotif = $notificationRepository->findOneBy(['id' => $id]);
        $selectedNotif->setIsRead(true);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('notification');
    }


    public function addNotif($receiver, $type = "divers", $content = null, $duty = null, $origin = null){
        $user = $this->getUser();
        $dutyRepository = $this->getDoctrine()->getRepository(Duty::class);
        $notification = new Notification();

        switch ($type) {
            case 'proposition':
                $dutySelected = $dutyRepository->findOneBy(['id' => $duty]);
                $content = $user->getName(). "Vous propose de l'aide concernant votre offre ".$dutySelected->getTitle();
                break;

            case 'warning' :
                $content = $content;
                break;
                
            case 'verification': 
                $content = "Vous avez des éléments à vérifier dans l'espace administrateur ";
            
            default:
                $content = $content;
                break;
        }


        $notification->setContent($content);
        $notification->setCreatedAt(new \DateTime('now'));
        $notification->setIsRead(false);
        $notification->setMember($user);
        $notification->setType('proposition');
        $notification->setOriginMember($origin);
        $notification->setDuty($dutySelected);


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($notification);
        $entityManager->flush();
    }
    
    
    /**
     * @Route("/notification/help/{asker}-{duty}", name="notification_offers_help")
    */
    public function offersHelp($asker, $duty, NotificationRepository $notificationRepository, DutyRepository $dutyRepository)
    {
        $user = $this->getUser();
        $this-> addNotif($user, "proposition", null, $duty, $user);

        return $this->redirectToRoute('notification');
    }
}
