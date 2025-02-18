<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserConfirmationEmailNotReceived;
use App\Event\UserRegistered;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EmailVerifier $emailVerifier, EventDispatcherInterface $eventDispatcher)
    {
        $this->emailVerifier = $emailVerifier;
        $this->eventDispatcher = $eventDispatcher;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $event = new UserRegistered($user);
            $this->eventDispatcher->dispatch($event, UserRegistered::class);

            // do anything else you need here, like send an email
            $this->addFlash('success', 'Registration successful.');

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_advertisement_index');
    }

    #[Route('/verify', name: 'app_ask_confirm')]
    public function validateUserMail(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->redirectToRoute('app_advertisement_index');
        }

        $form = $this->createFormBuilder()
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary w-100']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new UserConfirmationEmailNotReceived($user);
            $this->eventDispatcher->dispatch($event, UserConfirmationEmailNotReceived::class);

            $this->addFlash('success', 'A new confirmation email sent.');
        }

        return $this->render('registration/demande_confirmation.html.twig', ['form' => $form]);
    }
}
