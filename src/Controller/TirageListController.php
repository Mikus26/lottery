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
}
