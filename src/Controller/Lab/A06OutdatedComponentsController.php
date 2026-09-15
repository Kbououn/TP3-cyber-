<?php

namespace App\Controller\Lab;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A06:2021 - Vulnerable and Outdated Components.
 *
 * Cette page ne contient pas de faille exploitable elle-même : c'est
 * l'ensemble du projet (composer.lock, Dockerfile) qui illustre cette
 * catégorie. Voir composer.json pour les versions volontairement anciennes,
 * et le Dockerfile pour l'image PHP de base obsolète.
 *
 * C'est la seule catégorie de ce TP que Trivy peut détecter automatiquement
 * (analyse SCA) : les autres routes /lab/aXX relèvent du SAST/DAST/relecture
 * manuelle de code, hors du périmètre de Trivy.
 */
class A06OutdatedComponentsController extends AbstractController
{
    #[Route('/lab/a06', name: 'lab_a06_index')]
    public function index(): Response
    {
        return $this->render('lab/a06/index.html.twig');
    }
}
