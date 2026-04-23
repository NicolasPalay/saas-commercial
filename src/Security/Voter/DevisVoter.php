<?php

// src/Security/Voter/DevisVoter.php

namespace App\Security\Voter;

use App\Entity\Devis;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DevisVoter extends Voter
{
    public const VIEW = 'DEVIS_VIEW';
    public const EDIT = 'DEVIS_EDIT';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Devis;
    }

    protected function voteOnAttribute(string $attribute, mixed $devis, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($devis, $user),
            self::EDIT => $this->canEdit($devis, $user),
            default => false,
        };
    }

    private function canView(Devis $devis, User $user): bool
    {
        return $devis->getCompany() === $user->getCompany();
    }

    private function canEdit(Devis $devis, User $user): bool
    {
        return $this->canView($devis, $user);
    }
}