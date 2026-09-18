<?php

namespace App\Controller\Lab;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// ATTENTION : clé codée en dur, volontairement laissée ici pour la démo Trivy --scanners secret
// (voir Cours Trivy - TP2). Format valide d'une clé d'accès AWS, mais fictive.
// AWS_ACCESS_KEY_ID=AKIAQWERTYUIOPASDFGH

/**
 * OWASP A05:2021 - Security Misconfiguration.
 *
 * - /lab/a05/phpinfo expose phpinfo() publiquement (versions, chemins, config serveur).
 * - /_profiler et la barre de débogage Symfony restent actifs (voir .env : APP_ENV=dev
 *   et APP_DEBUG=1 même dans l'image "de production").
 * - /lab/a05/api/users renvoie du JSON avec un header CORS `Access-Control-Allow-Origin: *`.
 * - Le Dockerfile ne définit aucun utilisateur non-root (voir docker/Dockerfile).
 */
class A05MisconfigController extends AbstractController
{
    #[Route('/lab/a05', name: 'lab_a05_index')]
    public function index(): Response
    {
        return $this->render('lab/a05/index.html.twig', [
            'appEnv' => $_SERVER['APP_ENV'] ?? 'inconnu',
            'appDebug' => $_SERVER['APP_DEBUG'] ?? 'inconnu',
        ]);
    }

    #[Route('/lab/a05/phpinfo', name: 'lab_a05_phpinfo')]
    public function phpinfo(): Response
    {
        ob_start();
        phpinfo();
        $html = ob_get_clean();

        return new Response($html);
    }

    #[Route('/lab/a05/api/users', name: 'lab_a05_api_users')]
    public function apiUsers(UserRepository $users): JsonResponse
    {
        $data = array_map(static fn ($u) => [
            'id' => $u->getId(),
            'email' => $u->getEmail(),
            // Le hash du mot de passe ne devrait jamais transiter par une API "publique".
            'passwordHash' => $u->getPassword(),
            'roles' => $u->getRoles(),
        ], $users->findAll());

        $response = new JsonResponse($data);
        // CORS permissif : n'importe quel site tiers peut lire cette réponse en JS.
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
