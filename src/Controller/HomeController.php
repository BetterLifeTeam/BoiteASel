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

        $slider = $memberRepository->getSlider(1);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'slider' => $slider[0]
        ]);
    }
}
