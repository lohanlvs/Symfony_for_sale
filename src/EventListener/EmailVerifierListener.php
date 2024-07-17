<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\UserConfirmationEmailNotReceived;
use App\Event\UserRegistered;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserRegistered::class, method: 'onUserRegisteredEvent')]
#[AsEventListener(event: UserConfirmationEmailNotReceived::class, method: 'onUserConfirmationEvent')]
class EmailVerifierListener
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    public function onUserRegisteredEvent(UserRegistered $event): void
    {
        $user = $event->getUser();

        if ($user->isVerified()) {
            return;
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }

    public function onUserConfirmationEvent(UserConfirmationEmailNotReceived $event): void
    {
        $user = $event->getUser();

        if ($user->isVerified()) {
            return;
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
