<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwoFactorController extends AbstractController
{
    private GoogleAuthenticatorInterface $googleAuthenticator;
    private EntityManagerInterface $entityManager;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $entityManager)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->entityManager = $entityManager;
    }

    #[Route('/enable_2fa', name: 'app_2fa_form')]
    public function setup2FA(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('auth_code');

            if ($this->googleAuthenticator->checkCode($user, $code)) {
                $user->setTwoFactorEnabled(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', '2FA activée avec succès!');

                return $this->redirectToRoute('app_profile');
            }
        }

        $qrCodeContent = $this->googleAuthenticator->getQRContent($user);
        $qrCodeBase64 = $this->generateQRCode($qrCodeContent);

        return $this->render('security/2fa_form.html.twig', [
            'qrCodeBase64' => $qrCodeBase64,
        ]);
    }
    private function generateQRCode(string $qrCodeContent): string
    {
        $qrCode = new QrCode($qrCodeContent);

        $qrCode->getSize(200);
        $qrCode->getMargin(10);

        $writer = new PngWriter();

        return base64_encode($writer->write($qrCode)->getString());
    }

    #[Route('/2fa_check', name: '2fa_login_check')]
    public function check2FA(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('auth_code');

            if ($this->googleAuthenticator->checkCode($user, $code)) {
                $this->addFlash('success', 'Authentification réussie!');
                return $this->redirectToRoute('app_profile');
            } else {
                $this->addFlash('error', 'Le code est incorrect, veuillez réessayer.');
            }
        }

        return $this->render('security/2fa_setup.html.twig');
    }

    #[Route('/disable_2fa', name: 'app_disable_2fa')]
    public function disable2FA(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_profile');
        }

        if ($request->isMethod('POST')) {
            $user->setTwoFactorEnabled(false);
            $user->setGoogleAuthenticatorSecret(null);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'authentification à deux facteurs a été désactivée avec succès.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/disable_2fa.html.twig', [
            'user' => $user,
        ]);
    }
}