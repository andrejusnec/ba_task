<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     *
     * @throws TransportExceptionInterface
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('addresses');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('ba_addressbook_task2021@gmail.com', 'Security'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            $this->addFlash('success', 'Verification email has been send. In order to log in - verify you\'r email');

            return $this->redirectToRoute('user');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $loginFormAuthenticator
    ): Response {
        $id = $request->get('id');
        if (null === $id) {
            return $this->redirectToRoute('login');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('login');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('success', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');
        $userAuthenticator->authenticateUser($user, $loginFormAuthenticator, $request);

        return $this->redirectToRoute('addresses');
    }

    /**
     * @Route("/verify/resend", name="app_email_resend")
     *
     * @throws TransportExceptionInterface
     */
    public function resendEmail(Request $request, UserRepository $userRepository): RedirectResponse
    {
        $email = $request->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);
        if (null !== $user && !$user->isVerified()) {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('ba_addressbook_task2021@gmail.com', 'Security'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            $this->addFlash('success', 'Verification email has been send.');
        }

        return $this->redirectToRoute('app_login');
    }
}
