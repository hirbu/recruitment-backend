<?php

namespace App\MessageHandler;

use App\Entity\Resume;
use App\Message\ProcessResumeMessage;
use App\Service\ResumeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessResumeMessageHandler
{
    public function __construct(
        private readonly ResumeService          $resumeService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(ProcessResumeMessage $message)
    {
        $resume = $this->entityManager->find(
            Resume::class,
            $message->getResumeId()
        );

        $data = $this->resumeService->parseResume($resume);

        $resume->setExtra($data);

        $this->entityManager->persist($resume);
        $this->entityManager->flush();
    }
}
