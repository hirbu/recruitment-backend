<?php

namespace App\Controller;

use App\Dto\ResumeUploadRequest;
use App\Entity\Resume;
use App\Security\ResumeVoter;
use App\Service\ResumeService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @codeCoverageIgnore
 */
#[Route('/api/resumes')]
class ResumeController extends AbstractController
{
    public function __construct(
        private readonly ResumeService $resumeService,
    )
    {
    }

    #[Route('/upload', name: 'resume_upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        $result = $this->resumeService->processUpload($request);

        return $this->json($result);
    }

    #[Route('/{id}/download', name: 'resume_download', methods: ['GET'])]
    #[IsGranted(ResumeVoter::DOWNLOAD, 'resume')]
    public function download(Resume $resume): Response
    {
        $result = $this->resumeService->processDownload($resume);

        return $this->file($result);
    }
} 