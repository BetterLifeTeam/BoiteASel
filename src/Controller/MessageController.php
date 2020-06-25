<?php

namespace App\Controller;

use App\Entity\Duty;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\Conversation;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/message")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/", name="message_index", methods={"GET"})
     */
    public function index(MessageRepository $messageRepository): Response
    {
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{duty}-{asker}", name="message_new", methods={"GET","POST"})
     */
    public function new($duty, $asker, Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $dutyRepository = $this->getDoctrine()->getRepository(Duty::class);
            $d = $dutyRepository->findOneBy(['id' => $duty]);

            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $askerInfo = $memberRepository->findOneBy(['id' => $asker]);

            $conversationRepository = $this->getDoctrine()->getRepository(Conversation::class);
            $conv1 = $conversationRepository->findOneBy(['member1' => $user, 'member2' => $askerInfo]);
            $conv2 = $conversationRepository->findOneBy(['member1' => $askerInfo, 'member2' => $user]);

            
            // Création du message
            $message->setCreatedAt(new \DateTime('now'));
            $message->setSender($user);

            //Check si une conversation entre les 2 personnes existent déja
            if($conv1 || $conv2){
                if($conv1){
                    $conv1->addMessage($message);
                    $message->setConversation($conv1);
                }else{
                    $conv2->addMessage($message);
                    $message->setConversation($conv2);
                }
           }else{
                // Création de la conversation
                $conversation = new Conversation();
                $conversation->setDuty($d);
                $conversation->setCreatedAt(new \DateTime('now'));
                $conversation->setMember1($askerInfo);
                $conversation->setMember2($user);

                $message->setConversation($conversation);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($conversation);
                $entityManager->flush();
           }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('message_index');
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="message_show", methods={"GET"})
     */
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="message_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Message $message): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('message_index');
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="message_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Message $message): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('message_index');
    }
}
