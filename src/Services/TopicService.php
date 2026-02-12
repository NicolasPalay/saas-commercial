<?php

namespace App\Services;

use App\Entity\Conversation;
use Symfony\Component\HttpFoundation\RequestStack;

class TopicService
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {}

    public function getTopicUrl(Conversation $conversation): string
    {
       return '/conversations/' . $conversation->getId();
        }

    private function getServicerUrl(): string
    {
        $request = $this->requestStack->getMainRequest();
        $scheme= $request->getScheme();
        $host = $request->getHost();
        $port = $request->getPort();
        $port = $port ? ":{$port}" : "";

        return $scheme . '://' . $host . $port;

    }
}
