<?php

namespace App\Controller\Lab;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A04:2021 - Insecure Design.
 *
 * /lab/a04/checkout fait confiance à un champ de formulaire caché envoyé
 * par le client pour connaître le prix à débiter : aucun recalcul côté
 * serveur à partir du catalogue -> manipulation de prix triviale.
 *
 * Voir aussi /login (Cours OWASP A04) : aucune limitation du nombre de
 * tentatives de connexion, aucun CAPTCHA, aucun verrouillage de compte.
 */
class A04InsecureDesignController extends AbstractController
{
    private const CATALOG = [
        'formation-symfony' => ['label' => 'Formation Symfony (5 jours)', 'price' => 1500.00],
        'support-1an' => ['label' => 'Support technique 1 an', 'price' => 300.00],
    ];

    #[Route('/lab/a04/checkout', name: 'lab_a04_checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request): Response
    {
        $confirmation = null;

        if ($request->isMethod('POST')) {
            $productId = (string) $request->request->get('product_id', '');
            // VULNERABLE : le prix vient du formulaire (champ caché modifiable), pas du catalogue serveur.
            $price = (float) $request->request->get('price', 0);

            $confirmation = [
                'product' => self::CATALOG[$productId]['label'] ?? $productId,
                'price' => $price,
            ];
        }

        return $this->render('lab/a04/checkout.html.twig', [
            'catalog' => self::CATALOG,
            'confirmation' => $confirmation,
        ]);
    }
}
