<?php

namespace App\Security;

use App\Entity\Resume;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ResumeVoter extends Voter
{
    public const DOWNLOAD = 'RESUME_DOWNLOAD';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::DOWNLOAD && $subject instanceof Resume;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Resume $resume */
        $resume = $subject;

        // Get the associated application
        $application = $resume->getApplication();
        if (!$application) {
            return false;
        }

        // Get the posting from the application
        $posting = $application->getPosting();
        if (!$posting) {
            return false;
        }

        // Check if the current user is the owner of the posting
        return $posting->getOwner() === $user;
    }
}
