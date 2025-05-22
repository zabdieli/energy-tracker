<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Goal;
use App\Entity\ConsumptionRecord;
use Doctrine\ORM\EntityManagerInterface;

final class AlertControllerTest extends WebTestCase
{
    public function testIndexWithAlert(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Création d'un utilisateur fictif
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed_password'); // Un mot de passe quelconque (non utilisé ici)
        $em->persist($user);

        // Objectif avec une limite à 50 kWh
        $goal = new Goal();
        $goal->setUser($user);
        $goal->setLimite(50);
        $em->persist($goal);

        // Enregistrement d'une consommation au-dessus de la limite
        $record = new ConsumptionRecord();
        $record->setUser($user);
        $record->setValue(75); // Dépasse la limite
        $record->setDate(new \DateTime('now')); 
        $em->persist($record);

        $em->flush();

        // Authentifie l'utilisateur dans le test
        $client->loginUser($user);

        // Requête vers la route protégée
        $client->request('GET', '/alerts');

        // Vérifie que la page s'affiche
        self::assertResponseIsSuccessful();

        // Vérifie qu'une alerte s'affiche bien dans le rendu HTML
        self::assertSelectorTextContains('li.list-group-item-danger', 'consommation');
    }
}
