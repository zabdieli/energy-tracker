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

        // On récupère tous les enregistrements de consommation de l'utilisateur
        $records = $recordRepo->findBy(['user' => $user]);

        // On récupère l'objectif avec la limite de consommation fixée
        $goal = $goalRepo->findOneBy(['user' => $user]);
        if (!$goal) {
            throw new \Exception("Aucun objectif trouvé pour cet utilisateur.");
        }

        // Supposons que la méthode getLimite() retourne la limite personnalisée en kWh
        $customLimit = $goal->getLimite();

        foreach ($records as $record) {
            $value = $record->getValue();
            $dateTime = $record->getDate()->format('Y-m-d');

            if ($value > $customLimit) {
                $alerts[] = "⚠️ Alerte à $dateTime : consommation de {$value} kWh (limite : $customLimit kWh)";
            }
        }

        return $this->render('alert/index.html.twig', [
            'alerts' => $alerts,
        ]);
    }
}
