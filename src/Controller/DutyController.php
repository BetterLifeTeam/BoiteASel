<?php

namespace App\Controller;

use App\Entity\Duty;
use App\Form\DutyType;
use App\Repository\DutyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/duty")
 */
class DutyController extends AbstractController
{
    /**
     * @Route("/", name="duty_index", methods={"GET"})
     */
    public function index(DutyRepository $dutyRepository): Response
    {
        return $this->render('duty/index.html.twig', [
            'duties' => $dutyRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="duty_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $duty = new Duty();
        $form = $this->createForm(DutyType::class, $duty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($duty);
            $entityManager->flush();

            return $this->redirectToRoute('duty_index');
        }

        return $this->render('duty/new.html.twig', [
            'duty' => $duty,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="duty_show", methods={"GET"})
     */
    public function show(Duty $duty): Response
    {
        return $this->render('duty/show.html.twig', [
            'duty' => $duty,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="duty_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Duty $duty): Response
    {
        $form = $this->createForm(DutyType::class, $duty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('duty_index');
        }

        return $this->render('duty/edit.html.twig', [
            'duty' => $duty,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="duty_delete", methods={"DELETE"})
     */
    /*
    public function delete(Request $request, Duty $duty): Response
    {
        if ($this->isCsrfTokenValid('delete'.$duty->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($duty);
            $entityManager->flush();
        }

        return $this->redirectToRoute('duty_index');
    }
    */
}
