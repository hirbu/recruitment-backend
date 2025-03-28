<?php

namespace App\Controller;

use App\Entity\Posting;
use App\Security\PostingVoter;
use App\Service\ApplicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Annotation\Context;

/**
 * @codeCoverageIgnore
 */
#[Route('/api')]
class ApplicationController extends AbstractController
{
    public function __construct(
        private readonly ApplicationService $applicationService
    )
    {
    }

    #[Route('/postings/{id}/applications', name: 'get_posting_applications', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted(PostingVoter::OWNER, subject: 'posting')]
    public function getPostingApplications(Posting $posting): Response
    {
        $applications = $this->applicationService->getApplicationsByPosting($posting);

        return $this->json($applications, Response::HTTP_OK, context: ['groups' => ['application:read']]);
    }
}
