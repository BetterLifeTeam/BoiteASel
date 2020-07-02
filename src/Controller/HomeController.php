<?php

namespace App\Controller;

use App\Repository\MemberRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(MemberRepository $memberRepository)
    {

        $slider = $memberRepository->getSlider(0);

        if ($this->getUser()) {
            $this->redirectToRoute("duty_search");
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'slider' => $slider[0]
        ]);
    }
}
