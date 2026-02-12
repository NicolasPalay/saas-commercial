<?php

namespace App\Factory;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;

class MessageFactory
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function create(string $content, User $user, Conversation $conversation): Message
    {
        $message = new Message();
        $message->setConversation($conversation);
        $message->setContent($content);
        $message->setUser($user);
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }
}