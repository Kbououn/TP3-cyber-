<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NOTE PEDAGOGIQUE : authentification "maison", écrite à la main sans passer
 * par le composant Security de Symfony. C'est en soi une mauvaise pratique
 * (Insecure Design - OWASP A04) : on réinvente un mécanisme sensible au lieu
 * d'utiliser celui, éprouvé, du framework.
 *
 * Volontairement présents dans ce contrôleur (à des fins pédagogiques) :
 *  - Pas de régénération de l'identifiant de session à la connexion (fixation de session - A07)
 *  - Enumération d'utilisateurs via des messages d'erreur différents (A07)
 *  - Aucune limitation de tentatives / pas de verrouillage de compte (A04, A07)
 *  - Journalisation du mot de passe fourni en clair en cas d'échec (A09)
 *  - Cookie "se souvenir de moi" prévisible et non expirant (A07)
 *  - Récupération de mot de passe par question secrète devinable, réponse en clair (A02, A04)
 */
class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, UserRepository $users, LoggerInterface $logger): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = (string) $request->request->get('email', '');
            $password = (string) $request->request->get('password', '');
            $rememberMe = (bool) $request->request->get('remember_me');

            $user = $users->findOneByEmail($email);

            if (!$user) {
                // Enumération de comptes : on confirme que l'email n'existe pas.
                $logger->info(sprintf('Echec de connexion : utilisateur inconnu "%s"', $email));
                $error = 'Aucun compte ne correspond à cet email.';
            } elseif (md5($password) !== $user->getPassword()) {
                // Le mot de passe fourni en clair est journalisé : fuite de secret dans les logs (A09).
                $logger->info(sprintf(
                    'Echec de connexion pour "%s" avec le mot de passe fourni "%s"',
                    $email,
                    $password
                ));
                $error = 'Mot de passe incorrect.';
            } else {
                $session = $request->getSession();
                // Pas de régénération de l'id de session après authentification -> fixation de session.
                $session->set('user_id', $user->getId());
                $session->set('is_admin', $user->isAdmin());

                $response = $this->redirectToRoute('app_home');

                if ($rememberMe) {
                    // Jeton "remember me" prévisible (md5 de l'email) et durée de vie de 10 ans,
                    // sans flag Secure/HttpOnly explicite ni rotation -> vol/forge de session facile.
                    $response->headers->setCookie(new Cookie(
                        'remember_token',
                        md5($user->getEmail()),
                        strtotime('+10 years'),
                        '/',
                        null,
                        false,
                        false
                    ));
                }

                return $response;
            }
        }

        return $this->render('auth/login.html.twig', ['error' => $error]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): RedirectResponse
    {
        $request->getSession()->invalidate();

        return $this->redirectToRoute('app_home');
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $em): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = (string) $request->request->get('email', '');
            $password = (string) $request->request->get('password', '');
            $securityAnswer = (string) $request->request->get('security_answer', '');

            // Aucune règle de complexité, aucune vérification de force du mot de passe (A02/A04).
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(md5($password));
            $user->setRoles(['ROLE_USER']);
            $user->setSecurityAnswer($securityAnswer);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig', ['error' => $error]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, UserRepository $users, EntityManagerInterface $em): Response
    {
        $step = 'email';
        $user = null;
        $error = null;
        $revealedPassword = null;

        $email = (string) $request->request->get('email', '');

        if ($request->isMethod('POST') && $request->request->has('answer')) {
            $user = $users->findOneByEmail((string) $request->request->get('email_hidden', ''));
            $answer = (string) $request->request->get('answer', '');

            // Comparaison en clair d'une "question secrète" -> devinable, souvent réutilisée (A02/A04).
            if ($user && strcasecmp((string) $user->getSecurityAnswer(), $answer) === 0) {
                $newPassword = substr(md5((string) random_int(0, PHP_INT_MAX)), 0, 10);
                $user->setPassword(md5($newPassword));
                $em->flush();
                $step = 'done';
                // Le nouveau mot de passe est ré-affiché en clair à l'écran (devrait être envoyé par email).
                $revealedPassword = $newPassword;
            } else {
                $error = 'Réponse incorrecte.';
                $step = 'question';
            }
        } elseif ($request->isMethod('POST')) {
            $user = $users->findOneByEmail($email);
            $step = $user ? 'question' : 'email';
            if (!$user) {
                $error = 'Aucun compte ne correspond à cet email.';
            }
        }

        return $this->render('auth/forgot_password.html.twig', [
            'step' => $step,
            'user' => $user,
            'error' => $error,
            'revealedPassword' => $revealedPassword,
        ]);
    }
}
