<?php

declare(strict_types=1);

namespace App\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface {

    private string $defaultLocale;

    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 17]]
        ];
    }

    public function onKernelRequest(RequestEvent $event){
        $request = $event->getRequest();

        if(!$request->hasPreviousSession()){
            return;
        }

        $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
    }
}