<?php

namespace App\Service;

use App\Repository\ConsumptionRecordRepository;
use App\Repository\GoalRepository;
use App\Entity\User;

class AlertService
{
    private $recordRepo;
    private $goalRepo;

    public function __construct(ConsumptionRecordRepository $recordRepo, GoalRepository $goalRepo)
    {
        $this->recordRepo = $recordRepo;
        $this->goalRepo = $goalRepo;
    }

    public function getAlerts(User $user): array
    {
        $alerts = [];
        $records = $this->recordRepo->findBy(['user' => $user]);

        $goal = $this->goalRepo->findOneBy(['user' => $user]);
        if (!$goal) {
            throw new \Exception("Aucun objectif trouvé pour cet utilisateur.");
        }

        $customLimit = $goal->getLimite();

        foreach ($records as $record) {
            $value = $record->getValue();
            $dateTime = $record->getDate()->format('Y-m-d');

            if ($value > $customLimit) {
                $alerts[] = "Alerte à $dateTime : consommation de {$value} kWh (limite : $customLimit kWh)";
            }
        }

        return $alerts;
    }
}