<?php

namespace App\Controller\Lab;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A08:2021 - Software and Data Integrity Failures.
 *
 * - /lab/a08/preferences sérialise les préférences avec PHP serialize()
 *   et les stocke dans un cookie en clair, puis les relit avec unserialize()
 *   sans aucune validation -> désérialisation non sûre / PHP Object Injection.
 * - /lab/a08/upload accepte n'importe quel fichier, sans vérifier son
 *   extension ni son type MIME, et le stocke dans public/uploads/ (donc
 *   directement accessible et potentiellement exécutable).
 */
class A08IntegrityController extends AbstractController
{
    #[Route('/lab/a08/preferences', name: 'lab_a08_preferences', methods: ['GET', 'POST'])]
    public function preferences(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $prefs = [
                'theme' => (string) $request->request->get('theme', 'light'),
                'language' => (string) $request->request->get('language', 'fr'),
            ];

            $response = $this->redirectToRoute('lab_a08_preferences');
            // VULNERABLE : objet PHP sérialisé, stocké côté client, sans signature ni chiffrement.
            $response->headers->setCookie(new Cookie('prefs', base64_encode(serialize($prefs))));

            return $response;
        }

        $prefs = null;
        $raw = $request->cookies->get('prefs');
        if ($raw) {
            // VULNERABLE : unserialize() sur une donnée qui vient entièrement du client.
            $prefs = unserialize(base64_decode($raw));
        }

        return $this->render('lab/a08/preferences.html.twig', ['prefs' => $prefs]);
    }

    #[Route('/lab/a08/upload', name: 'lab_a08_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request): Response
    {
        $uploadedPath = null;

        $file = $request->files->get('document');
        if ($file) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            // VULNERABLE : aucune whitelist d'extension/MIME, nom de fichier client conservé tel quel.
            $filename = $file->getClientOriginalName();
            $file->move($uploadsDir, $filename);
            $uploadedPath = '/uploads/' . $filename;
        }

        return $this->render('lab/a08/upload.html.twig', ['uploadedPath' => $uploadedPath]);
    }
}
