<?php

namespace App\Services;

use App\Entity\Company;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;

class MessagingStatisticsService
{
    public function __construct(
        private ConversationRepository $conversationRepository,
        private MessageRepository $messageRepository
    ) {}

    /**
     * Obtenir les conversations récentes de l'utilisateur
     */
    public function getRecentConversations($user, int $limit = 5): array
    {
        return $this->conversationRepository->findRecentByUser($user, $limit);
    }

    /**
     * Obtenir les statistiques de messagerie pour l'entreprise
     */
    public function getMessagingStats(Company $company): array
    {
        // Total conversations
        $totalConversations = $this->conversationRepository->countByCompany($company);
        
        // Total messages
        $totalMessages = $this->messageRepository->countByCompany($company);
        
        // Messages aujourd'hui
        $today = new \DateTime('today');
        $todayMessages = $this->messageRepository->countByCompanyAndDate($company, $today);
        
        // Conversations actives (avec messages cette semaine)
        $weekAgo = new \DateTime('-7 days');
        $activeConversations = $this->conversationRepository->countActiveByCompany($company, $weekAgo);
        
        return [
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'todayMessages' => $todayMessages,
            'activeConversations' => $activeConversations,
        ];
    }

    /**
     * Obtenir les conversations non lues
     */
    public function getUnreadConversationCount($user): int
    {
        return $this->conversationRepository->countUnreadByUser($user);
    }

    /**
     * Obtenir le dernier message d'une conversation
     */
    public function getLastMessage($conversation)
    {
        if ($conversation->getMessage()->count() > 0) {
            return $conversation->getMessage()->last();
        }
        return null;
    }

    /**
     * Obtenir les participantes d'une conversation (sauf l'utilisateur actuel)
     */
    public function getOtherParticipants($conversation, $currentUser)
    {
        return $conversation->getUsers()->filter(function($user) use ($currentUser) {
            return $user->getId() !== $currentUser->getId();
        })->getValues();
    }
}
