<?php

namespace App\Controller\Lab;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A07:2021 - Identification and Authentication Failures.
 *
 * Les failles concrètes sont dans App\Controller\AuthController (/login,
 * /register, /forgot-password) : pas de régénération de session, pas de
 * limitation de tentatives, énumération d'utilisateurs, cookie "remember me"
 * prévisible. Cette page se contente d'afficher l'état de session courant
 * pour observer ces défauts en direct.
 */
class A07AuthFailuresController extends AbstractController
{
    #[Route('/lab/a07', name: 'lab_a07_index')]
    public function index(Request $request): Response
    {
        return $this->render('lab/a07/index.html.twig', [
            'sessionId' => $request->getSession()->getId(),
            'userId' => $request->getSession()->get('user_id'),
            'rememberToken' => $request->cookies->get('remember_token'),
        ]);
    }
}
