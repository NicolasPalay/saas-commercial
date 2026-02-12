<?php

namespace App\Twig\Components;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DevisComponent
{
    use DefaultActionTrait;

    #[LiveProp()]
    public ?string $entity = null;

    #[LiveProp()]
    public? array $headers = [];


    public array $lines = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security)
    {
    }

    public function mount(string $entity, array $headers): void{
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }
        $repository = $this->entityManager->getRepository($entity);
        $this->lines = $repository->findBy(['company' => $user->getCompany()]);
        $this->entity = $entity;
        $this->headers = $headers;
    }

    #[LiveAction]
    public function sort(#[LiveArg] string $header, #[LiveArg] string $direction): void {
        $repository = $this->entityManager->getRepository($this->entity);
        $this->lines = $repository->findBy(['company' => $this->security->getUser()->getCompany()], [$header => $direction]);
    }
}

