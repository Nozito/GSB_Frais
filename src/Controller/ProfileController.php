<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface; // Add this line for EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Add this for Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // Use Annotation instead of Attribute

class ProfileController extends AbstractController
{
    private $entityManager;

    // The EntityManagerInterface should be the one from Doctrine
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request): Response
    {
        // Get the currently logged-in user
        /** @var User $user */
        $user = $this->getUser();

        // Ensure the user is authenticated
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $isTwoFactorEnabled = $user->isTwoFactorEnabled();

        if ($request->isMethod('POST')) {
            // Get the form type submitted
            $formType = $request->request->get('2fa_form');

            if ($formType === '1') {
                // Redirect to the 2FA configuration page
                return $this->redirectToRoute('app_2fa_form');
            } elseif ($formType === '0') {
                // If the user is deactivating 2FA
                if ($user->isTwoFactorEnabled()) {
                    $user->setTwoFactorEnabled(false);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    // Add a flash success message
                    $this->addFlash('success', 'L\'authentification à deux facteurs a été désactivée.');
                }
            }
        }

        // Render the profile view with the user data
        return $this->render('security/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/2fa_qrcode', name: 'app_2fa_qrcode')]
    public function show2FAQrCode(): Response
    {
        return $this->render('security/2fa_setup.html.twig');
    }
}