<?php

namespace App\Controller;

use App\Entity\Duty;
use App\Form\DutyType;
use App\Form\DutyTypeType;
use App\Form\SearchDutyType;
use App\Entity\DutyType as DutyT;
use App\Repository\DutyRepository;
use App\Repository\DutyTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @Route("/search", name="duty_search", methods={"GET","POST"})
     */
    public function search(Request $request): Response
    {
        $searchDutyType = $this->createForm(SearchDutyType::class);
        $searchDutyType->handleRequest($request);

        $repoTypes = $this->getDoctrine()->getRepository(DutyT::class);
        $types = $repoTypes->findAll();

        $dutyRepository = $this->getDoctrine()->getRepository(Duty::class);

        if ($searchDutyType->isSubmitted() && $searchDutyType->isValid()) {
            $data = $searchDutyType->getData();
            $search = $data['search'];
            $order = $data['order'];
            $type = $data['type'];

            if(is_null($order)){
                $order = 'DESC';
            }

            if(is_null($type)){
                $duties = $dutyRepository->findByKey($search, $order);
            } else { 

                $duties = $dutyRepository->findByKeyAndType($search, $order, $type->getId());
            }
        } else {
            // $duties = $dutyRepository->findAllWithoutSetback();
            $duties = $dutyRepository->findAllWithoutSetback();
        } 
        
        return $this->render('duty/search.html.twig', [
            'search_form' => $searchDutyType->createView(),
            'duties' => $duties,
        ]);
    }

    /**
     * @Route("/new", name="duty_new", methods={"GET","POST"})
     */
    public function new($type = null, Request $request): Response
    {
        $duty = new Duty();
        $form = $this->createForm(DutyType::class, $duty);
        $form->handleRequest($request);

        $user = $this->getUser();

        // Form to add new duty
        if ($form->isSubmitted() && $form->isValid()) {
            $duty->setCreatedAt(new \DateTime('now'));
            $duty->setStatus('not checked');
            $duty->setAsker($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($duty);
            $entityManager->flush();

            return $this->redirectToRoute('duty_search');
        }

        return $this->render('duty/new.html.twig', [
            'duty' => $duty,
            'form' => $form->createView(),
            // 'formType' => '',
        ]);
    }

    /**
     * @Route("/{id}", name="duty_show", methods={"GET","POST"})
     */
    public function show($id, Request $request, Duty $duty): Response
    {
        $dutyType = new DutyT();
        $form = $this->createForm(DutyTypeType::class, $dutyType);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $dutyType->setStatus(false);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($dutyType);
            $entityManager->flush();

            return $this->redirectToRoute('duty_show', array("id" => $id));
        }

        return $this->render('duty/show.html.twig', [
            'duty' => $duty,
            'form' => $form->createView(),
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

            return $this->redirectToRoute('member_duties', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('duty/edit.html.twig', [
            'duty' => $duty,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="duty_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Duty $duty): Response
    {
        if ($this->isCsrfTokenValid('delete'.$duty->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($duty);
            $entityManager->flush();
        }

        return $this->redirectToRoute('duty_index');
    }
}
