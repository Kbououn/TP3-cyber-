<?php

namespace App\Controller\Lab;

use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A02:2021 - Cryptographic Failures.
 *
 * - Mots de passe utilisateurs hashés en MD5 sans sel (voir App\Entity\User).
 * - Jetons "API" (JWT) signés avec un secret faible, codé en dur, et sans
 *   expiration : facilement rejouable, cassable hors-ligne, ou forgeable.
 * - Utilise firebase/php-jwt en version volontairement ancienne (voir composer.json)
 *   qui contient de vraies CVE connues (à retrouver avec Trivy / composer audit).
 */
class A02CryptoController extends AbstractController
{
    // Secret JWT volontairement faible et codé en dur (devrait venir d'un coffre à secrets).
    private const JWT_SECRET = 'secret';

    #[Route('/lab/a02', name: 'lab_a02_index')]
    public function index(UserRepository $users): Response
    {
        return $this->render('lab/a02/index.html.twig', [
            'users' => $users->findAll(),
        ]);
    }

    #[Route('/lab/a02/jwt/issue', name: 'lab_a02_jwt_issue')]
    public function issue(Request $request, UserRepository $users): Response
    {
        $email = (string) $request->query->get('email', 'admin@poc-owasp.local');
        $user = $users->findOneByEmail($email);

        $token = null;
        if ($user) {
            $payload = [
                'sub' => $user->getEmail(),
                'roles' => $user->getRoles(),
                // Pas de claim "exp" : le jeton n'expire jamais.
            ];
            $token = JWT::encode($payload, self::JWT_SECRET, 'HS256');
        }

        return $this->render('lab/a02/jwt_issue.html.twig', [
            'email' => $email,
            'user' => $user,
            'token' => $token,
        ]);
    }

    #[Route('/lab/a02/jwt/verify', name: 'lab_a02_jwt_verify')]
    public function verify(Request $request): Response
    {
        $token = (string) $request->query->get('token', '');
        $decoded = null;
        $error = null;

        if ($token !== '') {
            try {
                $decoded = JWT::decode($token, self::JWT_SECRET, ['HS256']);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('lab/a02/jwt_verify.html.twig', [
            'token' => $token,
            'decoded' => $decoded,
            'error' => $error,
        ]);
    }
}
