<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\AdminEditMemberType;
use App\Form\AdminNewMemberType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MemberRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    //Permet d'accéder à la liste des membres
    /**
     * @Route("/members", name="admin_members_list")
     */
    public function displayMembers(MemberRepository $memberRepository, Request $request, UserPasswordEncoderInterface $encoder){

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
            $newRoles[] = "ROLE_MEMBER";

            // Remplissage avec les valeurs par défaut
            $member->setMoney(300);
            $member->setRoles($newRoles);
            var_dump($password);
            $member->setPassword($hash);

            /**
             * Ici on enverra un mail au nouveau membre avec ses identifiants en lui conseillant de changer le mot de passe
             */


            $entityManager->persist($member);
            $entityManager->flush();

            return $this->redirectToRoute('admin_members_list');
        }

        return $this->render('admin/members.html.twig', [
            'members' => $memberRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    //Permet de voir les infos sur le membre
    /**
     * @Route("/members/{id}", name="admin_member_show", methods={"GET"})
     */
    public function show(Member $member): Response
    {
        return $this->render('admin/member_show.html.twig', [
            'member' => $member,
        ]);
    }

    //Permet d'éditer le nombre de grains de sel du membre
    /**
     * @Route("/members/{id}/edit", name="admin_member_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(AdminEditMemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_members_list');
        }

        return $this->render('admin/member_edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/members/{id}", name="admin_member_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Member $member): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($member);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_members_list');
    }
}
