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
use App\Service\AlertService;
use Symfony\Component\HttpFoundation\RedirectResponse;
final class AlertController extends AbstractController
{
    private $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    #[Route('/alerts', name: 'alerts')]
    public function index(Security $security): Response
    {
        $user = $security->getUser();
        $alerts = $this->alertService->getAlerts($user);

        return $this->render('alert/index.html.twig', [
            'alerts' => $alerts,
        ]);
    }
}