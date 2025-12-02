<?php

namespace App\Controller;

use App\Entity\TirageList;
use App\Form\TirageListType;
use App\Repository\TirageListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tirage/list')]
final class TirageListController extends AbstractController
{
    #[Route(name: 'app_tirage_list_index', methods: ['GET'])]
    public function index(TirageListRepository $tirageListRepository): Response
    {
        return $this->render('tirage_list/index.html.twig', [
            'tirage_lists' => $tirageListRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tirage_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tirageList = new TirageList();
        $form = $this->createForm(TirageListType::class, $tirageList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tirageList);
            $entityManager->flush();

            return $this->redirectToRoute('app_tirage_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tirage_list/new.html.twig', [
            'tirage_list' => $tirageList,
            'form' => $form,
        ]);
    }

    #[Route('/import', name: 'app_tirage_list_import', methods: ['GET'])]
    public function import(EntityManagerInterface $em): Response
    {
        $file = $this->getParameter('kernel.project_dir') . '/public/doc/euromillions.csv';

        if (!file_exists($file)) {
            return new Response("CSV introuvable : $file", 404);
        }

        if (($handle = fopen($file, 'r')) === false) {
            return new Response("Impossible d’ouvrir le fichier CSV.", 500);
        }

        // === RÉCUPÉRER LES HEADERS EN IGNORANT LES LIGNES VIDES ===
        $headers = null;
        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            // ligne vide ou juste un retour chariot
            if ($line === [null] || (count($line) === 1 && trim((string) $line[0]) === '')) {
                continue;
            }
            $headers = $line;
            break;
        }

        if ($headers === null) {
            return new Response("En-têtes CSV invalides ou fichier vide.", 400);
        }

        $count = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            if (count($data) !== count($headers)) {
                continue;
            }

            $row = array_combine($headers, $data);
            if ($row === false) {
                continue;
            }

            $tirage = new TirageList();

            // DATE -> dateTirage
            $tirage->setDateTirage(new \DateTime($row['DATE']));

            // N1..N5 -> numeroUn..numeroCinq
            $tirage->setNumeroUn($row['N1']);
            $tirage->setNumeroDeux($row['N2']);
            $tirage->setNumeroTrois($row['N3']);
            $tirage->setNumeroQuatre($row['N4']);
            $tirage->setNumeroCinq($row['N5']);

            // E1, E2 -> etoileUn, etoileDeux
            $tirage->setEtoileUn($row['E1']);
            $tirage->setEtoileDeux($row['E2']);

            $em->persist($tirage);
            $count++;

            if ($count % 50 === 0) {
                $em->flush();
                $em->clear();
            }
        }

        fclose($handle);

        $em->flush();
        $em->clear();

        return new Response("Import terminé : $count lignes insérées.");
    }

    #[Route('/{id}/edit', name: 'app_tirage_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TirageList $tirageList, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TirageListType::class, $tirageList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tirage_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tirage_list/edit.html.twig', [
            'tirage_list' => $tirageList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tirage_list_delete', methods: ['POST'])]
    public function delete(Request $request, TirageList $tirageList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tirageList->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tirageList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tirage_list_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/stats', name: 'app_tirage_list_stats', methods: ['GET'])]
    public function stats(TirageListRepository $repo): Response
    {
        $tirages = $repo->findAll();

        if (!$tirages) {
            return new Response("Aucun tirage en base.", 200);
        }

        $total = count($tirages);

        // tableaux de comptage
        $counts = [
            'numeroUn'     => [],
            'numeroDeux'   => [],
            'numeroTrois'  => [],
            'numeroQuatre' => [],
            'numeroCinq'   => [],
            'etoileUn'     => [],
            'etoileDeux'   => [],
        ];

        foreach ($tirages as $t) {
            $n1 = (int) $t->getNumeroUn();
            $n2 = (int) $t->getNumeroDeux();
            $n3 = (int) $t->getNumeroTrois();
            $n4 = (int) $t->getNumeroQuatre();
            $n5 = (int) $t->getNumeroCinq();
            $e1 = (int) $t->getEtoileUn();
            $e2 = (int) $t->getEtoileDeux();

            $counts['numeroUn'][$n1]     = ($counts['numeroUn'][$n1]     ?? 0) + 1;
            $counts['numeroDeux'][$n2]   = ($counts['numeroDeux'][$n2]   ?? 0) + 1;
            $counts['numeroTrois'][$n3]  = ($counts['numeroTrois'][$n3]  ?? 0) + 1;
            $counts['numeroQuatre'][$n4] = ($counts['numeroQuatre'][$n4] ?? 0) + 1;
            $counts['numeroCinq'][$n5]   = ($counts['numeroCinq'][$n5]   ?? 0) + 1;
            $counts['etoileUn'][$e1]     = ($counts['etoileUn'][$e1]     ?? 0) + 1;
            $counts['etoileDeux'][$e2]   = ($counts['etoileDeux'][$e2]   ?? 0) + 1;
        }

        // on transforme en [valeur, count, proba] et on trie par fréquence desc
        $result = [];
        foreach ($counts as $position => $values) {
            arsort($values); // trie décroissant sur le nombre d'apparitions

            $result[$position] = [];
            foreach ($values as $num => $count) {
                $result[$position][] = [
                    'valeur'           => $num,
                    'occurences'       => $count,
                    'proba_empirique'  => $count / $total,
                ];
            }
        }

        // --- petite boucle de "prédiction" pondérée par les fréquences ---
        $prediction = [];

        foreach ($result as $position => $values) {
            $pool = [];

            // on répète chaque valeur selon son nombre d'occurrences
            foreach ($values as $entry) {
                for ($i = 0; $i < $entry['occurences']; $i++) {
                    $pool[] = $entry['valeur'];
                }
            }

            // tirage aléatoire pondéré (si jamais vide, null par sécurité)
            $prediction[$position] = !empty($pool)
                ? $pool[array_rand($pool)]
                : null;
        }

        // structure finale de tirage "prévu"
        $tiragePrevu = [
            'numeros' => [
                $prediction['numeroUn']     ?? null,
                $prediction['numeroDeux']   ?? null,
                $prediction['numeroTrois']  ?? null,
                $prediction['numeroQuatre'] ?? null,
                $prediction['numeroCinq']   ?? null,
            ],
            'etoiles' => [
                $prediction['etoileUn']   ?? null,
                $prediction['etoileDeux'] ?? null,
            ],
        ];

        return $this->render('tirage_list/stats.html.twig', [
            'total' => $total,
            'top' => [
                'numeroUn'     => $result['numeroUn'][0]     ?? null,
                'numeroDeux'   => $result['numeroDeux'][0]   ?? null,
                'numeroTrois'  => $result['numeroTrois'][0]  ?? null,
                'numeroQuatre' => $result['numeroQuatre'][0] ?? null,
                'numeroCinq'   => $result['numeroCinq'][0]   ?? null,
                'etoileUn'     => $result['etoileUn'][0]     ?? null,
                'etoileDeux'   => $result['etoileDeux'][0]   ?? null,
            ],
            'prediction' => $tiragePrevu,
        ]);
    }
}
