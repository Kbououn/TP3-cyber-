<?php

namespace App\Controller\Lab;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A09:2021 - Security Logging and Monitoring Failures.
 *
 * - Les tentatives de connexion échouées journalisent le mot de passe fourni
 *   en clair (voir App\Controller\AuthController::login) : une donnée
 *   sensible se retrouve dans un fichier de log.
 * - Cette page lit et affiche directement le fichier de log applicatif,
 *   sans aucune authentification -> exposition de données sensibles à
 *   quiconque connaît l'URL.
 */
class A09LoggingController extends AbstractController
{
    #[Route('/lab/a09', name: 'lab_a09_index')]
    public function index(): Response
    {
        $logFile = $this->getParameter('kernel.project_dir') . '/var/log/' . $this->getParameter('kernel.environment') . '.log';
        $lines = [];

        if (is_file($logFile)) {
            $content = (string) file_get_contents($logFile);
            $allLines = explode("\n", trim($content));
            $lines = array_slice($allLines, -60);
        }

        return $this->render('lab/a09/index.html.twig', [
            'logFile' => $logFile,
            'lines' => $lines,
        ]);
    }
}
