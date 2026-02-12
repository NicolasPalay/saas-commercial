<?php

namespace App\Controller;

use App\DTO\CreateMessage;
use App\Entity\Message;
use App\Entity\User;
use App\Factory\MessageFactory;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\ConversationRepository;
use App\Services\TopicService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[Route('/messages')]
final class MessageController extends AbstractController
{
    public function __construct(
        private readonly MessageFactory $messageFactory,
        private readonly ConversationRepository $conversationRepository,
        private readonly HubInterface $hub,
        private readonly TopicService $topicService

    )
    {
    }

    #[Route('index',name: 'app_message_index', methods: ['GET'])]
    public function index(MessageRepository $messageRepository): Response
    {
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }
   #[Route('/create', name: 'message.create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new Response('Invalid user', 401);
        }
        $content = $request->request->get('content');
        $conversationId = $request->request->get('conversationId');

        if (!$content || !$conversationId) {
            return new Response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new Response('Conversation not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $this->messageFactory->create(
                content: $content,
                user: $this->getUser(),
                conversation: $conversation
            );

            $data = [
                'content' => $content,
                'user' => $user->getEmail(),
                'conversationId' => $conversationId,
            ];

            $update = new Update(
                topics: $this->topicService->getTopicUrl($conversation),
                data: json_encode($data),
                private: true
            );

            $this->hub->publish($update);
        } catch (\Throwable $e) {
            return new Response($e->getMessage(), 500);
        }

        return new Response('', Response::HTTP_CREATED);
    }


    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}
