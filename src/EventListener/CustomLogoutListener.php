<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class CustomLogoutListener
{
    #[AsEventListener(event: LogoutEvent::class)]
    public function onLogoutEvent(LogoutEvent $event): void
    {
        $event->setResponse(new JsonResponse([]));
    }
}
