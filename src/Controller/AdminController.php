<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\AdminMemberType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MemberRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    // public function index()
    // {
    //     return $this->render('admin/index.html.twig', [
    //         'controller_name' => 'AdminController',
    //     ]);
    // }

    //Permet d'accéder à la liste des membres
    /**
     * @Route("/members", name="admin_members_list")
     */
    public function displayMembers(MemberRepository $memberRepository){
        return $this->render('admin/members.html.twig', [
            'members' => $memberRepository->findAll(),
        ]);
    }

    //Permet de voir les infos sur le membre
    /**
     * @Route("/{id}", name="admin_member_show", methods={"GET"})
     */
    public function show(Member $member): Response
    {
        return $this->render('admin/member_show.html.twig', [
            'member' => $member,
        ]);
    }

    //Permet d'éditer le nombre de grains de sel du membre
    /**
     * @Route("/{id}/edit", name="admin_member_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(AdminMemberType::class, $member);
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
}
