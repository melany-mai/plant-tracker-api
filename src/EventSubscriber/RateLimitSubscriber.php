<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $authLimiter,
        private RateLimiterFactory $registerLimiter,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();
        $ip = $request->getClientIp() ?? 'unknown';

        if ('/auth' === $path && $request->isMethod('POST')) {
            $limiter = $this->authLimiter->create($ip);
            if (!$limiter->consume(1)->isAccepted()) {
                throw new TooManyRequestsHttpException(60, 'Too many login attempts. Try again in 1 minute.');
            }
        }

        if ('/api/register' === $path && $request->isMethod('POST')) {
            $limiter = $this->registerLimiter->create($ip);
            if (!$limiter->consume(1)->isAccepted()) {
                throw new TooManyRequestsHttpException(3600, 'Too many registration attempts. Try again in 1 hour.');
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }
}
