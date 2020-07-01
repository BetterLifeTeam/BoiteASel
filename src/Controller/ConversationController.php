<?php

namespace App\Controller;

use App\Entity\Duty;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\Conversation;
use App\Form\ConversationType;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/conversation")
 */
class ConversationController extends AbstractController
{

    public function nice_dump($data)
    {
        highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");
    }

    /**
     * @Route("/", name="conversation_index", methods={"GET","POST"})
     * @Route("/{selectedConversation}", name="conversation_msg_index", methods={"GET","POST"})
     */
    public function index($selectedConversation = null, Request $request, ConversationRepository $conversationRepository): Response
    {        
        $user = $this->getUser();

        $conversations = $conversationRepository->findUserConversation($user);

        if(empty($conversations)){
            $this->nice_dump($conversations);
            $noForm = $this->createFormBuilder([]);
            return $this->render('conversation/index.html.twig', [
                'conversations' => $conversations,
                'selectedConversation' => "",
                'message' => array(),
                'form' => "",
            ]);
        }
        if($selectedConversation){
            $selectedConv = $conversationRepository->findOneBy(['id' => $selectedConversation]);
            $conversation = $selectedConv->getMessages();
        }else{
            $selectedConv = $conversations[0];
            $selectedConversation = $conversations[0]->getId();
            $conversation = $conversations[0]->getMessages();
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setCreatedAt(new \DateTime('now'));
            $message->setSender($user);

            $conv = $conversationRepository->findOneBy(['id' => $selectedConversation]);
            $conv->addMessage($message);
            $conv->setLastActivity(new \DateTime('now'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();
        }

        // if (isset($selectedConv) && $selectedConv) {
            return $this->render('conversation/index.html.twig', [
                'conversations' => $conversations,
                'message' => $conversation,
                'selectedConversation' => $selectedConv,
                'form' => $form->createView(),
            ]);
        // }

        // return $this->render('conversation/index.html.twig', [
        //     'conversations' => $conversations,
        //     'message' => $conversation,
        //     'selectedConversation' => $selectedConversation,
        //     'form' => $form->createView(),
        // ]);
    }

    /**
     * @Route("/newconv/{duty}-{asker}", name="new_conversation_index", methods={"GET","POST"})
     */
    public function indexNewConv($duty = null, $asker, Request $request, ConversationRepository $conversationRepository): Response
    {        
        $user = $this->getUser();

        $conversationRepository = $this->getDoctrine()->getRepository(Conversation::class);
        $conversations = $conversationRepository->findUserConversation($user);

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        $memberRepository = $this->getDoctrine()->getRepository(Member::class);
        $askerInfo = $memberRepository->findOneBy(['id' => $asker]);
        
        $conversationRepository = $this->getDoctrine()->getRepository(Conversation::class);
        $conv1 = $conversationRepository->findOneBy(['member1' => $user, 'member2' => $askerInfo]);
        $conv2 = $conversationRepository->findOneBy(['member1' => $askerInfo, 'member2' => $user]);

        if($conv1 || $conv2){
            if($conv1){
                $selectedConversation = $conv1;
                $convMessage = $conv1->getMessages();
            }else{
                $selectedConversation = $conv2;
                $convMessage = $conv2->getMessages();
            }
        }else{
            $selectedConversation = "";
            $convMessage = null;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $dutyRepository = $this->getDoctrine()->getRepository(Duty::class);
            $d = $dutyRepository->findOneBy(['id' => $duty]);
            
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
                $newConversation = new Conversation();
                $newConversation->setDuty($d);
                $newConversation->setCreatedAt(new \DateTime('now'));
                $newConversation->setLastActivity(new \DateTime('now'));
                $newConversation->setMember1($askerInfo);
                $newConversation->setMember2($user);

                $message->setConversation($newConversation);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newConversation);
                $entityManager->flush();
           }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('conversation_index');
        }

        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations,
            'message' => $convMessage,
            'form' => $form->createView(),
            'selectedConversation' => $selectedConversation,
        ]);
    }

    /**
     * @Route("/new", name="conversation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $conversation = new Conversation();
        $form = $this->createForm(ConversationType::class, $conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conversation->setCreatedAt(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conversation);
            $entityManager->flush();

            return $this->redirectToRoute('conversation_index');
        }

        return $this->render('conversation/new.html.twig', [
            'conversation' => $conversation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="conversation_show", methods={"GET"})
     */
    public function show(Conversation $conversation): Response
    {
        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="conversation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Conversation $conversation): Response
    {
        $form = $this->createForm(ConversationType::class, $conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('conversation_index');
        }

        return $this->render('conversation/edit.html.twig', [
            'conversation' => $conversation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="conversation_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Conversation $conversation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conversation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conversation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('conversation_index');
    }
}
