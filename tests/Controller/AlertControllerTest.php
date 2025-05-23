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

        
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed_password'); 
        $em->persist($user);

        
        $goal = new Goal();
        $goal->setUser($user);
        $goal->setLimite(50);
        $em->persist($goal);

       
        $record = new ConsumptionRecord();
        $record->setUser($user);
        $record->setValue(75);
        $record->setDate(new \DateTime('now')); 
        $em->persist($record);

        $em->flush();

        
        $client->loginUser($user);

        
        $client->request('GET', '/alerts');


        self::assertResponseIsSuccessful();

        
        self::assertSelectorTextContains('li.list-group-item-danger', 'consommation');
    }
}
