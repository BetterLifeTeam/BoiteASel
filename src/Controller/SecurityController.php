<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\RegistrationType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $member = new Member();
        $form = $this->createForm(RegistrationType::class, $member);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // Ne remonte pas dans security.yaml pour encoder en bcrypt ! a étudier après 

            $hash = $encoder->encodePassword($member, $member->getPassword());
            $member->setPassword($hash);

            $manager->persist($member);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
    */
    public function login(){
            return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
    */
    public function logout(){
        return $this->render('home/index.html.twig');
    }

}
