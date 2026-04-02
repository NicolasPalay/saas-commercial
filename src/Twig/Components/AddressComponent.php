<?php

namespace App\Twig\Components;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class AddressComponent
{
    use DefaultActionTrait;

    #[LiveProp()]
    public ?string $entity = null;

    #[LiveProp()]
    public? array $headers = [];

    #[LiveProp()]
    public ?Client $client = null;

    public array $lines = [];

    

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security, private LoggerInterface $logger)
    {
    }

    public function mount(string $entity, array $headers, object $client): void
    {
        $user = $this->security->getUser();
        $company = $user->getCompany();
          if (!$user instanceof User) return;

        $repository = $this->entityManager->getRepository($entity);
        $this->client = $client;
        $this->lines = $repository->findByClientSortedByAddress($company, $this->client);
        $this->entity = $entity;
        $this->headers = $headers;
    }

    #[LiveAction]
    public function sort(#[LiveArg] string $header, #[LiveArg] string $direction): void
    {
        $this->entityManager->clear(); // vide l'identity map
        
        $repository = $this->entityManager->getRepository($this->entity);
        $this->lines = $repository->findByClientSortedByAddress(
            $this->security->getUser()->getCompany(),
            $this->client,
            $header,
            $direction
        );
    }
}

