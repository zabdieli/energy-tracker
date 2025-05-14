<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ConsumptionRecordRepository;
use App\Repository\GoalRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\ConsumptionRecord;
use App\Entity\Goal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ConsumptionRecordForm;
use App\Form\GoalForm;

final class AlertController extends AbstractController
{
    #[Route('/alerts', name: 'alerts')]
public function index(ConsumptionRecordRepository $recordRepo, GoalRepository $goalRepo, Security $security): Response
{
    $user = $security->getUser();
    $alerts = [];

    // Regrouper les consommations par catégorie et mois
    $records = $recordRepo->findBy(['user' => $user]);
    $grouped = [];

    foreach ($records as $record) {
        $month = $record->getDate()->format('Y-m');
        $cat = $record->getCategory()->getName();

        $grouped[$cat][$month] = ($grouped[$cat][$month] ?? 0) + $record->getValue();
    }

    // Comparer aux objectifs
    $goals = $goalRepo->findBy(['user' => $user]);

    foreach ($goals as $goal) {
        $cat = $goal->getCategory()->getName();
        $month = $goal->getMonth();
        $consumed = $grouped[$cat][$month] ?? 0;

        if ($consumed > $goal->getTargetAmount()) {
            $alerts[] = "🚨 Dépassement : $cat en $month ($consommé vs objectif {$goal->getTargetAmount()})";
        }
    }

    // Optionnel : détecter une hausse brutale par rapport au mois précédent

    return $this->render('alert/index.html.twig', [
        'alerts' => $alerts,
    ]);
}

}
