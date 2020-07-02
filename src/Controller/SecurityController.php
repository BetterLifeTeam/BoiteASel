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
    // Espace de connexion
    /**
     * @Route("/connexion", name="security_login")
    */
    public function login(){
        return $this->render('security/login.html.twig');
    }

    // Espace de deconnexion
    /**
     * @Route("/deconnexion", name="security_logout")
    */
    public function logout(){
        return $this->render('home/index.html.twig');
    }

}
