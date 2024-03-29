<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Duty;

use App\Entity\Member;
use App\Entity\DutyType;
use App\Entity\Notification;
use App\Form\AdminEditDutyType;
use App\Form\AdminNewMemberType;
use App\Form\AdminEditMemberType;
use App\Repository\DutyRepository;
use App\Repository\MemberRepository;
use App\Form\SuperAdminEditMemberType;
use App\Repository\DutyTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    ###################### PARTIE GESTION DES MEMBRES #############################

    //Permet d'accéder à la liste des membres
    /**
     * @Route("/members", name="admin_members_list")
     */
    public function displayMembers(MemberRepository $memberRepository, Request $request, UserPasswordEncoderInterface $encoder, MailerInterface $mailer)
    {
    
        $member = new Member();
        $form = $this->createForm(AdminNewMemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            // Génération du mot de passe aléatoire
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $password = '';
            for ($i = 0; $i < 10; $i++) {
                $password .= $characters[rand(0, $charactersLength - 1)];
            }
            $member->setPassword($password);
            $hash = $encoder->encodePassword($member, $member->getPassword());

            $newRoles = $member->getRoles();
            $newRoles[] = "ROLE_ADMIN";

            // Remplissage avec les valeurs par défaut
            $member->setMoney(300);
            $member->setRoles($newRoles);
            $member->setPassword($hash);

            $email = (new Email())
                    ->from('boiteasel@gmail.com')
                    ->to($member->getEmail())
                    ->subject("Création de votre compte sur le site BoiteASel")
                    ->text("Yeah")
                    ->html("<p>Bienvenue sur le site Boîte à SEL</p>
                    <p>Nos administrateurs ont prit le soin de vous inscrire !</p>
                    <p>Connectez-vous avec cette adresse mail et le mot de passe suivant : ".$password." (Nous vous conseillons de changer votre mot de passe après votre première connexion)</p>");

            $mailer->send($email);

            $entityManager->persist($member);
            $entityManager->flush();

            return $this->redirectToRoute('admin_members_list');
        }

        return $this->render('admin/members/members.html.twig', [
            'members' => $memberRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    //Permet de voir les infos sur le membre
    /**
     * @Route("/members/{id}", name="admin_member_show", methods={"GET"})
     */
    public function showMember(Member $member): Response
    {
        return $this->render('admin/members/member_show.html.twig', [
            'member' => $member,
        ]);
    }

    //Permet d'éditer le nombre de grains de sel du membre
    /**
     * @Route("/members/{id}/edit", name="admin_member_edit", methods={"GET","POST"})
     */
    public function editMember(Request $request, Member $member): Response
    {
        if (in_array("ROLE_SUPER_ADMIN", $this->getUser()->getRoles())) {
            $form = $this->createForm(SuperAdminEditMemberType::class, $member);
        } elseif (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $form = $this->createForm(AdminEditMemberType::class, $member);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_members_list');
        }

        return $this->render('admin/members/member_edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    // Permet de supprimer un membre
    /**
     * @Route("/members/{id}", name="admin_member_delete", methods={"DELETE"})
     */
    public function deleteMember(Request $request, Member $member): Response
    {
        if ($this->isCsrfTokenValid('delete' . $member->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($member);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_members_list');
    }

    ######################## PARTIE VERIFICATION DES ANNONCES #############################

    // Affiche les annonces
    /**
     * @Route("/duties", name="duties_to_check", methods={"GET"})
     */
    public function displayDuties(DutyRepository $dutyRepository, MemberRepository $memberRepository): Response
    {

        $voters = $memberRepository->countAdminAndSupAdmin();

        return $this->render('admin/duties/duties.html.twig', [
            'duties' => $dutyRepository->findBy(
                [
                    'status' => [
                        'not checked',
                        'setback'
                    ]
                ],
                [
                    'status' => 'DESC',
                    'setbackAt' => 'ASC',
                    'createdAt' => 'ASC'
                ]
            ),
            'nbVoter' => $voters
        ]);
    }

    // Affiche les détails d'une annonce
    /**
     * @Route("/duties/{id}", name="admin_duty_show", methods={"GET", "POST"})
     */
    public function showDuty(Duty $duty, Request $request, $id): Response
    {

        $defaultData = ['commentaire' => 'Entrez votre commentaire'];
        $form = $this->createFormBuilder($defaultData)
            ->add('commentaire', TextareaType::class, ['label' => false])
            ->add('Laisser un commentaire', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $commentaries = $duty->getVoteCommentary();
            $commentaries[$this->getUser()->__toString()] = $data["commentaire"];
            $duty->setVoteCommentary($commentaries);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_duty_show', ["id" => $id]);
        }

        return $this->render('admin/duties/duty_show.html.twig', [
            'duty' => $duty,
            'commentaryForm' => $form->createView()
        ]);
    }

    // Verifie si l'admin a déja voté
    private function checkIfMemberAlreadyVoted($entity)
    {
            $yesVotes = $entity->getYesVote();
            $noVotes = $entity->getNoVote();
            if (in_array($this->getUser()->getId(), $yesVotes) || in_array($this->getUser()->getId(), $noVotes)) {
                return false;
            } else {
                return true;
            }
    }

    // Envoie du vote
    /**
     * @Route("/duties/{id}/{vote}", name="admin_duty_vote",requirements={"vote": "yes|no"}, methods={"GET"})
     */
    public function voteForDuty(Duty $duty, $id, $vote, DutyRepository $dutyRepository, MemberRepository $memberRepository): Response
    {

        $canVote = $this->checkIfMemberAlreadyVoted($duty);
        if ($canVote) {
            if ($vote == 'yes') {
                $yesVotes = $duty->getYesVote();
                $yesVotes[] = $this->getUser()->getId();
                $duty->setYesVote($yesVotes);
            } else {
                $noVotes = $duty->getNoVote();
                $noVotes[] = $this->getUser()->getId();
                $duty->setNoVote($noVotes);
            }

            $yesPourcent = count($duty->getYesVote()) * 100 / $memberRepository->countAdminAndSupAdmin();
            $noPourcent = count($duty->getNoVote()) * 100 / $memberRepository->countAdminAndSupAdmin();

            // Si après le vote le pourcentage atteint 40% de oui
            if ($yesPourcent >= 40) {
                // Si c'était une annonce encore non vérifiée
                if ($duty->getStatus() == "not checked") {
                    $duty->setStatus("checked");
                    $checkedAt = new DateTime;
                    $duty->setCheckedAt($checkedAt);
                } else /*Sinon si c'était une annonce mise en retrait */ {
                    // Il faut remettre le statut en place avant la mise en retrait...
                    // Regarder à reculons quelle date est la première avec une valeur

                    // Si on était après la validation de l'offerer
                    if ($duty->getOffererValidAt() != null) {
                        $duty->setStatus("offerer validation");
                    } elseif /* Si on en était après la validation de l'asker */ ($duty->getAskerValidAt() != null) {
                        $duty->setStatus("asker validation");
                    } else /* Sinon on en est au stade checked */ {
                        $duty->setStatus("checked");
                    }
                }

                $duty->setYesVote([]);
                $duty->setNoVote([]);
                $duty->setVoteCommentary([]);
            }

            if ($noPourcent >= 40) {
                $duty->setStatus("toDelete");
                $duty->setYesVote([]);
                $duty->setNoVote([]);
                $duty->setVoteCommentary([]);
            }

            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('duties_to_check');
    }

    // Permet de modifier les droits et les grains de SEL d'un membre
    /**
     * @Route("/duties/{id}/edit", name="admin_duty_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Duty $duty): Response
    {
        $form = $this->createForm(AdminEditDutyType::class, $duty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('duties_to_check');
        }

        return $this->render('admin/duties/duty_edit.html.twig', [
            'duty' => $duty,
            'form' => $form->createView(),
        ]);
    }

    // Permet de mettre en retrait une annonce
    /**
     * @Route("/setback/{id}", options={"expose"=true}, name="admin_setback", methods={"GET", "POST"})
     */
    public function setback($id, MemberRepository $memberRepository){
        $dutyRepository = $this->getDoctrine()->getRepository(Duty::class);
        $entityManager = $this->getDoctrine()->getManager();
        
        $duty = $dutyRepository->find($id);
        
        $duty->setStatus("setback")
            ->setSetbackAt(new DateTime());
        
        $entityManager->flush();

        $notification = new Notification();

        $notification->setMember($duty->getAsker())
                    ->setContent("Votre annonce ".$duty->getTitle()." vient d'être mise en retrait par ".$this->getUser()." pour le motif : ".$_POST["motif"])
                    ->setCreatedAt(new DateTime())
                    ->setIsRead(false)
                    ->setOriginMember($this->getUser())
                    ->setType("warning")
                    ->setDuty($duty);
        
        $entityManager->persist($notification);

        $admins = $memberRepository->getAdminAndSupAdmin();


        foreach ($admins as $admin) {
            $notif = new Notification();

            $notif->setMember($memberRepository->find($admin["id"]))
                ->setContent("Une annonce a été mise en retrait !")
                ->setCreatedAt(new \DateTime())
                ->setIsRead(false)
                ->setOriginMember($this->getUser())
                ->setType("verification");

            $entityManager->persist($notif);

        }

        $entityManager->flush();

        return $this->redirectToRoute("duty_search");
    }
    

    ######################## PARTIE VERIFICATION DES TYPES DE SERVICES #############################

    // Permet l'affichage des différents types d'annonces
    /**
     * @Route("/dutytypes", name="dutytypes_to_check", methods={"GET"})
     */
    public function displayDutyTypes(DutyTypeRepository $dutyTypeRepository, MemberRepository $memberRepository): Response
    {

        $voters = $memberRepository->countAdminAndSupAdmin();

        return $this->render('admin/dutytypes/dutytypes.html.twig', [
            'duty_types' => $dutyTypeRepository->findBy(
                [
                    'status' => '0'
                ],
                [
                    'askedAt' => 'ASC'
                ]
            ),
            'nbVoter' => $voters
        ]);
    }

    // Permet de voir les détails d'un type 
    /**
     * @Route("/dutytypes/{id}", name="admin_dutytype_show", methods={"GET", "POST"})
     */
    public function showDutyType(DutyType $dutytype, Request $request, $id): Response
    {

        $defaultData = ['commentaire' => 'Entrez votre commentaire'];
        $form = $this->createFormBuilder($defaultData)
            ->add('commentaire', TextareaType::class)
            ->add('Laisser un commentaire', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $commentaries = $dutytype->getVoteCommentary();
            $commentaries[$this->getUser()->__toString()] = $data["commentaire"];
            $dutytype->setVoteCommentary($commentaries);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_dutytype_show', ["id" => $id]);
        }

        return $this->render('admin/dutytypes/dutytype_show.html.twig', [
            'duty_type' => $dutytype,
            'commentaryForm' => $form->createView()
        ]);
    }


    // Envoie du vote pour la validation d'un type d'annonce
    /**
     * @Route("/dutytypes/{id}/{vote}", name="admin_dutytype_vote",requirements={"vote": "yes|no"}, methods={"GET"})
     */
    public function voteForDutyType(DutyType $dutytype, $id, $vote, DutyTypeRepository $dutytypeRepository, MemberRepository $memberRepository): Response
    {
        $canVote = $this->checkIfMemberAlreadyVoted($dutytype);
        if ($canVote) {
            if ($vote == 'yes') {
                $yesVotes = $dutytype->getYesVote();
                $yesVotes[] = $this->getUser()->getId();
                $dutytype->setYesVote($yesVotes);
            } else {
                $noVotes = $dutytype->getNoVote();
                $noVotes[] = $this->getUser()->getId();
                $dutytype->setNoVote($noVotes);
            }

            $yesPourcent = count($dutytype->getYesVote()) * 100 / $memberRepository->countAdminAndSupAdmin();
            $noPourcent = count($dutytype->getNoVote()) * 100 / $memberRepository->countAdminAndSupAdmin();

            // Si après le vote le pourcentage atteint 40% de oui
            if ($yesPourcent >= 40) {
                $dutytype->setStatus(true);
                $dutytype->setYesVote([]);
                $dutytype->setNoVote([]);
                $dutytype->setVoteCommentary([]);
            }
            
            // Si après le vote le pourcentage atteint 40% de non
            if ($noPourcent >= 40) {
                $dutytype->setStatus(false);
                $dutytype->setYesVote([]);
                $dutytype->setNoVote([]);
                $dutytype->setVoteCommentary([]);
            }

            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('dutytypes_to_check');
    }


    ######################## PARTIE TABLEAU DE BORD #############################

    // Affichage du tableau de bord
    /**
     * @Route("/dashboard", name="admin_dashboard", methods={"GET"})
     */
    public function dashboard(MemberRepository $memberRepository)
    {
        return $this->render('admin/dashboard/dashboard.html.twig', [
            'fiveGivers' => $memberRepository->getHelpers(5),
            'fiveAsker' => $memberRepository->getAskers(5),
            'twentyActualites' => $memberRepository->getActualites(20),
            'allDutyTypes' => $memberRepository->getTypeActivites(),
            'volumesEchanges' => $memberRepository->getVolumesEchanges(),
        ]);
    }

    // Tableau de la liste des membres
    /**
     * @Route("/dashboard/givers", name="admin_dashboard_givers", methods={"GET"})
     */
    public function dashboardGivers(MemberRepository $memberRepository)
    {
        return $this->render('admin/dashboard/dashboard_givers.html.twig', [
            'givers' => $memberRepository->getHelpers(),
        ]);
    }

    // Tableau de l'actualité
    /**
     * @Route("/dashboard/activity", name="admin_dashboard_activity", methods={"GET"})
     */
    public function dashboardActuality(MemberRepository $memberRepository)
    {
        return $this->render('admin/dashboard/dashboard_activity.html.twig', [
            'activities' => $memberRepository->getActualites(),
        ]);
    }
}
