<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Duty;

use App\Entity\Member;
use App\Form\AdminEditDutyType;
use App\Form\AdminNewMemberType;
use App\Form\AdminEditMemberType;
use App\Repository\DutyRepository;
use App\Repository\MemberRepository;
use App\Form\SuperAdminEditMemberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    public function nice_dump($data)
    {
        highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");
    }

    ###################### PARTIE GESTION DES MEMBRES #############################

    //Permet d'accéder à la liste des membres
    /**
     * @Route("/members", name="admin_members_list")
     */
    public function displayMembers(MemberRepository $memberRepository, Request $request, UserPasswordEncoderInterface $encoder)
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

            $member->setPassword("password");
            $hash = $encoder->encodePassword($member, $member->getPassword());


            $newRoles = $member->getRoles();
            $newRoles[] = "ROLE_ADMIN";

            // Remplissage avec les valeurs par défaut
            $member->setMoney(300);
            $member->setRoles($newRoles);
            $member->setPassword($hash);

            /**
             * Ici on enverra un mail au nouveau membre avec ses identifiants en lui conseillant de changer le mot de passe
             */


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
        } elseif(in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
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

    /**
     * @Route("/duties/{id}", name="admin_duty_show", methods={"GET", "POST"})
     */
    public function showDuty(Duty $duty, Request $request, $id): Response
    {

        $defaultData = ['commentaire' => 'Entrez votre commentaire'];
        $form = $this->createFormBuilder($defaultData)
            ->add('commentaire', TextareaType::class)
            ->add('Laisser un commentaire', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $commentaries = $duty->getVoteCommentary();
            $commentaries[$this->getUser()->__toString()] = $data["commentaire"];
            // $this->nice_dump($commentaries);
            $duty->setVoteCommentary($commentaries);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_duty_show', ["id" => $id]);
        }

        return $this->render('admin/duties/duty_show.html.twig', [
            'duty' => $duty,
            'commentaryForm' => $form->createView()
        ]);
    }

    private function checkIfMemberAlreadyVoted($entity, $voteFor, DutyRepository $dutyRepository)
    {
        if ($voteFor == "duty") {
            $yesVotes = $entity->getYesVote();
            $noVotes = $entity->getNoVote();
            if (in_array($this->getUser()->getId(), $yesVotes) || in_array($this->getUser()->getId(), $noVotes)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * @Route("/duties/{id}/{vote}", name="admin_duty_vote",requirements={"vote": "yes|no"}, methods={"GET"})
     */
    public function voteForDuty(Duty $duty, $id, $vote, DutyRepository $dutyRepository, MemberRepository $memberRepository): Response
    {

        $canVote = $this->checkIfMemberAlreadyVoted($duty, 'duty', $dutyRepository);
        // $this->nice_dump($canVote);
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
                    }elseif /* Si on en était après la validation de l'asker */ ($duty->getAskerValidAt() != null) {
                        $duty->setStatus("asker validation");
                    }else /* Sinon on en est au stade checked */ {
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

        } else {
            // $this->redirect
        }


        return $this->redirectToRoute('duties_to_check');
    }

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
    
    ######################## PARTIE TABLEAU DE BORD #############################

    /**
     * @Route("/dashboard", name="admin_dashboard", methods={"GET"})
     */
    public function dashboard(MemberRepository $memberRepository){
        return $this->render('admin/dashboard/dashboard.html.twig', [
            'fiveGivers' => $memberRepository->getHelpers(5),
            'fiveAsker' => $memberRepository->getAskers(5),
            'twentyActualites' => $memberRepository->getActualites(20),
            'allDutyTypes' => $memberRepository->getTypeActivites(),
            'volumesEchanges' => $memberRepository->getVolumesEchanges(),
        ]);
    }

    /**
     * @Route("/dashboard/givers", name="admin_dashboard_givers", methods={"GET"})
    */
    public function dashboardGivers(MemberRepository $memberRepository){
        return $this->render('admin/dashboard/dashboard_givers.html.twig', [
            'givers' => $memberRepository->getHelpers(),
        ]);
    }

    /**
     * @Route("/dashboard/activity", name="admin_dashboard_activity", methods={"GET"})
    */
    public function dashboardActuality(MemberRepository $memberRepository){
        return $this->render('admin/dashboard/dashboard_activity.html.twig', [
            'activities' => $memberRepository->getActualites(),
        ]);
    }
}
