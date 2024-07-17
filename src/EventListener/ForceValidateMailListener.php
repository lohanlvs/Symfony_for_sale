<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

#[AsEventListener]
class ForceValidateMailListener
{
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;

    public function __construct(UrlGeneratorInterface $urlGenerator, Security $security)
    {
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        /* @var $user User */
        $user = $this->security->getUser();
        if (null === $user || $user->isVerified()) {
            return;
        }

        $currentRoute = $request->attributes->get('_route');
        $excludedRoutes = ['app_verify_email', 'app_logout', 'app_login', 'app_ask_confirm'];

        if (in_array($currentRoute, $excludedRoutes)) {
            return;
        }
        $event->setResponse(new Response(null, Response::HTTP_FOUND, ['Location' => $this->generateUrl('app_ask_confirm')]));
    }

    private function generateUrl(string $routeName): string
    {
        return $this->urlGenerator->generate($routeName);
    }
}
