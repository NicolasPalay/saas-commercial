<?php


namespace App\Security\Voter;

use App\Entity\User;
use App\Contract\OwnedByCompanyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CompanyResourceVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof  OwnedByCompanyInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;

        // La vérification centrale : même entreprise ?
        return $subject->getCompany() === $user->getCompany();
    }
}