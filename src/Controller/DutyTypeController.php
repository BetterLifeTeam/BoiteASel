<?php

namespace App\Controller;

use App\Entity\DutyType;
use App\Form\DutyTypeType;
use App\Repository\DutyTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dutytype")
 */
class DutyTypeController extends AbstractController
{
    /**
     * @Route("/", name="duty_type_index", methods={"GET"})
     */
    public function index(DutyTypeRepository $dutyTypeRepository): Response
    {
        return $this->render('duty_type/index.html.twig', [
            'duty_types' => $dutyTypeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", options={"expose"=true}, name="duty_type_new", methods={"GET","POST"})
     */
    public function new(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $dutyType = new DutyType();
        
        $dutyType->setTitle($_POST["title"])
                ->setHourlyPrice($_POST["price"])
                ->setStatus(0)
                ->setCreator($this->getUser())
                ->setAskedAt(new \DateTime());
        
        $em->persist($dutyType);
        $em->flush();

        return $this->redirectToRoute("duty_new");
    }

    /**
     * @Route("/{id}", name="duty_type_show", methods={"GET"})
     */
    public function show(DutyType $dutyType): Response
    {
        return $this->render('duty_type/show.html.twig', [
            'duty_type' => $dutyType,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="duty_type_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DutyType $dutyType): Response
    {
        $form = $this->createForm(DutyTypeType::class, $dutyType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('duty_type_index');
        }

        return $this->render('duty_type/edit.html.twig', [
            'duty_type' => $dutyType,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="duty_type_delete", methods={"DELETE"})
     */
    public function delete(Request $request, DutyType $dutyType): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dutyType->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($dutyType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('duty_type_index');
    }
}
