<?php

namespace App\MessageHandler;

use App\Entity\Application;
use App\Message\ProcessApplicationMessage;
use App\Service\ApplicationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessApplicationMessageHandler
{
    public function __construct(
        private readonly ApplicationService     $applicationService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(ProcessApplicationMessage $message)
    {
        $application = $this->entityManager->find(
            Application::class,
            $message->getApplicationId()
        );

        $this->applicationService->sendEmail($application);
    }
}
