<?php

namespace App\EventListener;

use App\Entity\Application;
use App\Service\PdfScorerService;
use App\Service\ResumeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Exception;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Application::class)]
class ApplicationCreateNotifier
{
    public function __construct(
        private readonly ResumeService          $resumeService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function postPersist(Application $application, PostPersistEventArgs $args): void
    {
        $this->processApplication($application);
    }

    /**
     * Process the application by scoring the resume and updating the score
     */
    private function processApplication(Application $application): void
    {
        $resume = $application->getResume();
        $posting = $application->getPosting();
        $tags = $posting->getTags()->map(fn($tag) => $tag->getName())->toArray();
        
        $score = $this->resumeService->scoreResume($resume, $tags);
        $application->setScore($score);

        $this->entityManager->persist($application);
        $this->entityManager->flush();
    }
}