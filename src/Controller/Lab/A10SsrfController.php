<?php

namespace App\Controller\Lab;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A10:2021 - Server-Side Request Forgery (SSRF).
 *
 * /lab/a10/preview simule une fonctionnalité "aperçu de lien" : le serveur
 * va chercher lui-même l'URL fournie par l'utilisateur et en affiche le
 * contenu, sans aucune liste blanche ni filtrage des IP privées/internes.
 * Depuis Docker, cette route peut par exemple interroger d'autres
 * conteneurs du réseau interne (http://db:3306, etc.).
 */
class A10SsrfController extends AbstractController
{
    #[Route('/lab/a10/preview', name: 'lab_a10_preview')]
    public function preview(Request $request): Response
    {
        $url = (string) $request->query->get('url', '');
        $body = null;
        $error = null;
        $status = null;

        if ($url !== '') {
            try {
                // VULNERABLE : aucune validation de l'hôte/du schéma/de la plage d'IP.
                $client = new Client(['timeout' => 3]);
                $res = $client->request('GET', $url);
                $status = $res->getStatusCode();
                $body = substr((string) $res->getBody(), 0, 2000);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('lab/a10/preview.html.twig', [
            'url' => $url,
            'status' => $status,
            'body' => $body,
            'error' => $error,
        ]);
    }
}
