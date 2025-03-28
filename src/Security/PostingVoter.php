<?php

namespace App\Security;

use App\Entity\Posting;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostingVoter extends Voter
{
    public const OWNER = 'POSTING_OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::OWNER && $subject instanceof Posting;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Posting $posting */
        $posting = $subject;
        return $posting->getOwner() === $user;
    }
} 
