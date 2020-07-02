<?php

namespace App\Controller;

use App\Entity\Duty;
use App\Entity\Member;
use App\Entity\Notification;
use App\Repository\DutyRepository;
use App\Repository\MemberRepository;
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
        $propositions = $notificationRepository->findBy(['member' => $user, 'isRead' => false ]);

        return $this->render('notification/index.html.twig', [
            'propositions' => $propositions,
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


    public function readNotif($id){
        var_dump("notification");
        var_dump($id);
        $notificationRepository = $this->getDoctrine()->getRepository(Notification::class);
        $selectedNotif = $notificationRepository->findOneBy(['id' => $id]);
        $selectedNotif->setIsRead(true);
        $this->getDoctrine()->getManager()->flush();
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
            case 'validation':
                $dutySelected = $dutyRepository->findOneBy(['id' => $duty]);
                $content = $user->getName(). " a acceptée votre proposition d'aide. Est-vous sûr de confirmer votre choix ? Annonce en réference : ".$dutySelected->getTitle();
                break;
            case 'done':
                $dutySelected = $dutyRepository->findOneBy(['id' => $duty]);
                $content = "Félicitation votre offre à trouver un aideur !  Veuillez nous confirmer quand la tâche a était confirmer, afin, de procéder à l'échange de grain de sel !";
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
        $notification->setMember($receiver);
        $notification->setType($type);
        $notification->setOriginMember($user);
        $notification->setDuty($dutySelected);


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($notification);
        $entityManager->flush();
    }
    
    
    /**
     * @Route("/notification/help/{asker}-{duty}", name="notification_offers_help")
    */
    public function offersHelp($asker, $duty, NotificationRepository $notificationRepository, DutyRepository $dutyRepository, MemberRepository $memberRepository)
    {
        $user = $this->getUser();
        $askerSelected = $memberRepository->findOneBy(['id' => $asker]);
        $this-> addNotif($askerSelected, "proposition", null, $duty, $user);

        return $this->redirectToRoute('notification');
    }

    /**
     * @Route("/notification/accept/{id}-{offer}-{duty}", name="notification_accept_offers")
    */
    public function acceptOffers($id, $offer, $duty, NotificationRepository $notificationRepository, DutyRepository $dutyRepository, MemberRepository $memberRepository)
    {
        $memberSelected = $memberRepository->findOneBy(['id' => $offer]);
        $dutySelected = $dutyRepository->findOneBy(['id' => $duty]);

        $dutySelected->setAskerValidAt(new \DateTime('now'));
        $dutySelected->setStatus('asker_validation');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($dutySelected);
        $entityManager->flush();

        $user = $this->getUser();
        $this->addNotif($memberSelected, "validation", null, $dutySelected, $user);

        $this->readNotif($id);

        return $this->redirectToRoute('notification');
    }

    /**
     * @Route("/notification/confirm/{id}-{asker}-{duty}", name="notification_confirm")
    */
    public function confirmOffers($id, $asker, $duty, NotificationRepository $notificationRepository, DutyRepository $dutyRepository, MemberRepository $memberRepository)
    {
        $user = $this->getUser();
        $memberSelected = $memberRepository->findOneBy(['id' => $asker]);
        $dutySelected = $dutyRepository->findOneBy(['id' => $duty]);
    
        $dutySelected->setOffererValidAt(new \DateTime('now'));
        $dutySelected->setOfferer($user);
        $dutySelected->setStatus('offerer_validation');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($dutySelected);
        $entityManager->flush();

        $this-> addNotif($memberSelected, "done", null, $dutySelected, $user);

        $this->readNotif($id);

        return $this->redirectToRoute('notification');
    }

    /**
     * @Route("/notification/done/{id}-{offer}-{duty}", name="notification_done")
    */
    public function dutyDone($id, $offer, $duty, NotificationRepository $notificationRepository, DutyRepository $dutyRepository, MemberRepository $memberRepository)
    {
        // GAIN EN JEU
        $dutySelected = $dutyRepository->findOneBy(['id' => $duty]);
        $dutySelected->setStatus('finished');
        $gain = $dutySelected->getPrice();

        // RECUPERATION DU ASKER
        $user = $this->getUser();
        $askerMoney = $user->getMoney();
        $user->setMoney($askerMoney - $gain);

        // RECUPERATION DU OFFER
        $offerSelected = $memberRepository->findOneBy(['id' => $offer]);
        $offerMoney = $offerSelected->getMoney();
        $offerSelected->setMoney($offerMoney + $gain);

        //Lecture de la notif
        $this->readNotif($id);

        // ECHANGE DE GRAIN DE SEL
        return $this->redirectToRoute('notification');
    }

}
