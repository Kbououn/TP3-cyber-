<?php

namespace App\Controller\Lab;

use App\Entity\Comment;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OWASP A03:2021 - Injection.
 *
 * - /lab/a03/search : injection SQL, la requête est construite par
 *   concaténation de chaînes au lieu de paramètres liés.
 * - /lab/a03/comments : XSS stockée, le contenu est ré-affiché avec le
 *   filtre Twig |raw (aucun échappement) et sans aucune validation.
 * - /lab/a03/ping : injection de commande, l'entrée utilisateur est passée
 *   telle quelle à shell_exec().
 */
class A03InjectionController extends AbstractController
{
    #[Route('/lab/a03/search', name: 'lab_a03_search')]
    public function search(Request $request, Connection $connection): Response
    {
        $q = (string) $request->query->get('q', '');
        $results = [];
        $sql = null;

        if ($q !== '') {
            // VULNERABLE : concaténation directe de l'entrée utilisateur dans le SQL.
            $sql = "SELECT * FROM invoices WHERE label LIKE '%" . $q . "%'";
            $results = $connection->executeQuery($sql)->fetchAllAssociative();
        }

        return $this->render('lab/a03/search.html.twig', [
            'q' => $q,
            'sql' => $sql,
            'results' => $results,
        ]);
    }

    #[Route('/lab/a03/comments', name: 'lab_a03_comments', methods: ['GET', 'POST'])]
    public function comments(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $comment = new Comment();
            $comment->setAuthor((string) $request->request->get('author', 'anonyme'));
            // Aucune validation, aucun échappement, aucun jeton CSRF sur ce formulaire.
            $comment->setContent((string) $request->request->get('content', ''));
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('lab_a03_comments');
        }

        $comments = $em->getRepository(Comment::class)->findBy([], ['id' => 'DESC']);

        return $this->render('lab/a03/comments.html.twig', ['comments' => $comments]);
    }

    #[Route('/lab/a03/ping', name: 'lab_a03_ping')]
    public function ping(Request $request): Response
    {
        $host = (string) $request->query->get('host', '');
        $output = null;

        if ($host !== '') {
            $flag = str_starts_with(PHP_OS, 'WIN') ? '-n' : '-c';
            // VULNERABLE : l'entrée utilisateur est passée telle quelle au shell.
            $output = shell_exec('ping ' . $flag . ' 2 ' . $host . ' 2>&1');
        }

        return $this->render('lab/a03/ping.html.twig', [
            'host' => $host,
            'output' => $output,
        ]);
    }
}
