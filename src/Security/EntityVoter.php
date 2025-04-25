<?php

namespace App\Security;

use App\Entity\Internship;
use App\Entity\Company;
use App\Entity\ActivityList;
use App\Entity\VisitReport;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EntityVoter extends Voter
{
    const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute !== self::EDIT) {
            return false;
        }

        return $subject instanceof Internship
            || $subject instanceof Company
            || $subject instanceof ActivityList
            || $subject instanceof VisitReport;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // Les professeurs peuvent tout modifier
        if (in_array('ROLE_TEACHER', $user->getRoles())) {
            return true;
        }

        // Le créateur peut modifier
        return $subject->getCreator() === $user;
    }
}
