<?php

namespace App\Controller\Lab;

use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A01:2021 - Broken Access Control.
 *
 * - /lab/a01/invoices/{id} : IDOR, aucune vérification que la facture
 *   consultée appartient bien à l'utilisateur connecté.
 * - /lab/a01/admin : vérifie seulement qu'un utilisateur est connecté,
 *   jamais son rôle -> élévation de privilèges verticale.
 */
class A01AccessControlController extends AbstractController
{
    #[Route('/lab/a01/invoices', name: 'lab_a01_invoices')]
    public function list(InvoiceRepository $invoices): Response
    {
        return $this->render('lab/a01/list.html.twig', [
            'invoices' => $invoices->findAll(),
        ]);
    }

    #[Route('/lab/a01/invoices/{id}', name: 'lab_a01_invoice_show', requirements: ['id' => '\d+'])]
    public function show(int $id, InvoiceRepository $invoices): Response
    {
        $invoice = $invoices->find($id);
        if (!$invoice) {
            throw $this->createNotFoundException();
        }

        // Pas de vérification que $invoice->getOwner() === l'utilisateur connecté : IDOR.
        return $this->render('lab/a01/show.html.twig', ['invoice' => $invoice]);
    }

    #[Route('/lab/a01/admin', name: 'lab_a01_admin')]
    public function admin(Request $request, UserRepository $users): Response
    {
        if (!$request->getSession()->get('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        // BUG VOLONTAIRE : on ne vérifie jamais $request->getSession()->get('is_admin').
        // N'importe quel utilisateur authentifié atteint donc cette page "admin".
        return $this->render('lab/a01/admin.html.twig', [
            'users' => $users->findAll(),
        ]);
    }
}
