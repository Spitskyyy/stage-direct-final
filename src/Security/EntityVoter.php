<?php

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EntityVoter extends Voter
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && method_exists($subject, 'getCreator');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Les rôles supérieurs ont toujours accès
        if ($this->security->isGranted('ROLE_TEACHER') || 
            $this->security->isGranted('ROLE_MODERATOR') || 
            $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Pour les étudiants, vérifier s'ils sont le créateur
        if ($this->security->isGranted('ROLE_STUDENT')) {
            return $subject->getCreator() === $user;
        }

        return false;
    }
}
