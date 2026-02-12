<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Form\ConversationType;
use App\Repository\MessageRepository;
use App\Repository\ConversationRepository;
use App\Services\TopicService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/conversation')]
final class ConversationController extends AbstractController
{
    public function __construct(
        private readonly Authorization $authorization,
        private readonly Discovery $discovery,
        private readonly TopicService $topicService)
    {
    }

    #[Route(name: 'app_conversation_index', methods: ['GET'])]
    public function index(ConversationRepository $conversationRepository): Response
    {
        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversationRepository->findAllByUser($this->getUser()),
        ]);
    }

    #[Route('/new', name: 'app_conversation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conversation = new Conversation();
        $company = $this->getUser()->getCompany();


        $form = $this->createForm(ConversationType::class, null, [
            'company' => $company,
            'current_user' => $this->getUser(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $conversation = $form->getData();
            $users = $form->get('users')->getData();
            if (!$users->contains($this->getUser())) {
                $users->add($this->getUser());
}
            foreach ($users as $user) {
                $conversation->addUser($user);

            }
            $conversation->addUser($this->getUser());

            $entityManager->persist($conversation);
            $entityManager->flush();

            return $this->redirectToRoute('app_conversation_show', ['id' => $conversation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conversation/new.html.twig', [
            'conversation' => $conversation,
            'form' => $form,

        ]);
    }

    #[Route('/{id}', name: 'app_conversation_show', methods: ['GET'])]
    public function show(Conversation $conversation, MessageRepository $messageRepository, Request $request): Response
    {
   $topic = $this->topicService->getTopicUrl($conversation);
dump($_ENV['MERCURE_PUBLIC_URL']);
dump($_ENV['MERCURE_URL']);
dump($topic);
    $this->discovery->addLink($request);
   //$this->authorization->setCookie($request, [$topic]);

         $messages = $messageRepository->findBy(['conversation' => $conversation], ['createdAt' => 'ASC']);
        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'topicUrl' => $topic
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conversation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conversation $conversation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConversationType::class, $conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_conversation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conversation/edit.html.twig', [
            'conversation' => $conversation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conversation_delete', methods: ['POST'])]
    public function delete(Request $request, Conversation $conversation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conversation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($conversation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_conversation_index', [], Response::HTTP_SEE_OTHER);
    }
}
