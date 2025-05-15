<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security; 
use App\Entity\ConsumptionRecord;
use App\Form\ConsumptionRecordTypeForm;
use App\Repository\ConsumptionRecordRepository;
use App\Service\OdreApiService;


final class ConsumptionController extends AbstractController
{
    #[Route('/consumption/new', name: 'consumption_new')]
    public function new(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $record = new ConsumptionRecord();

        $form = $this->createForm(ConsumptionRecordTypeForm::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $record->setUser($user);
            $em->persist($record);
            $em->flush();

            $this->addFlash('success', 'Consommation enregistrée avec succès.');
            return $this->redirectToRoute('consumption_new');
        }

        return $this->render('consumption/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/consumption/history', name: 'consumption_history')]
    public function history(ConsumptionRecordRepository $repo, Security $security): Response
    {
        $user = $security->getUser();
        $records = $repo->findBy(['user' => $user]);

        return $this->render('consumption/history.html.twig', [
            'records' => $records,
        ]);
    }

    #[Route('/consumption/chart', name: 'consumption_chart')]
    public function chart(ConsumptionRecordRepository $repo, Security $security): Response
    {
        $user = $security->getUser();
        $records = $repo->findBy(['user' => $user]);

        // Organiser les données : [Catégorie][Mois] => total
        $data = [];

        foreach ($records as $record) {
            $category = $record->getCategory()->getName();
            $month = $record->getDate()->format('Y-m'); 

            if (!isset($data[$category])) {
                $data[$category] = [];
            }

            if (!isset($data[$category][$month])) {
                $data[$category][$month] = 0;
            }

            $data[$category][$month] += $record->getValue();
        }

        // Générer liste de tous les mois présents (labels)
        $months = [];
        foreach ($data as $category => $monthData) {
            $months = array_merge($months, array_keys($monthData));
        }
        $months = array_unique($months);
        sort($months); // tri croissant

        return $this->render('consumption/chart.html.twig', [
            'data' => $data,
            'months' => $months,
        ]);
    }

    #[Route('/consumption/import-electricity', name: 'consumption_import_electricity')]
    public function importElectricity(OdreApiService $odreApiService): Response
    {
        $odreApiService->fetchAndStoreElectricityData();
        $this->addFlash('success', 'Données de consommation électrique importées avec succès.');
        return $this->redirectToRoute('consumption_history');
    }






}
