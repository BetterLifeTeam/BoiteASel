<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/member")
 */
class MemberController extends AbstractController
{
    // Profil de l'utilisateur connectée
    /**
     * @Route("/{id}", name="member_show", methods={"GET"})
     */
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', [
            'member' => $member,
            'asAsker' => count($member->getDutyAsAsker()),
            'asOfferer' => count($member->getDutyAsOfferer()),
        ]);
    }

    // Permet d'afficher les annonces qu'on a publié
    /**
     * @Route("/{id}/duties", name="member_duties", methods={"GET"})
     */
    public function myDuties(Member $member): Response
    {
        return $this->render('member/myduties.html.twig', [
            'member' => $member,
            'asAsker' => $member->getDutyAsAsker(),
        ]);
    }

    // Permet la modification de ses annonces
    /**
     * @Route("/{id}/edit", name="member_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_index');
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }
}
