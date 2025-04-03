<?php

namespace App\EventListener;

use App\Entity\Application;
use App\Message\ProcessApplicationMessage;
use App\Message\ProcessResumeMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Application::class)]
final class ApplicationCreateNotifier
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    )
    {
    }

    public function postPersist(Application $application, PostPersistEventArgs $args): void
    {
        $this->bus->dispatch(new ProcessApplicationMessage($application->getId()));

        $resume = $application->getResume();

        $this->bus->dispatch(new ProcessResumeMessage($resume->getId()));
    }
}
