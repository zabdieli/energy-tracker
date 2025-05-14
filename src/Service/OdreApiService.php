<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\ConsumptionRecord;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class OdreApiService
{
    private HttpClientInterface $client;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->em = $em;
    }

    public function fetchAndStoreElectricityData(): void
    {
        $response = $this->client->request(
            'GET',
            'https://odre.opendatasoft.com/api/explore/v2.1/catalog/datasets/consommation-quotidienne-brute/records?limit=30&order_by=date'
        );

        $data = $response->toArray();

        $category = $this->em->getRepository(Category::class)->findOneBy(['name' => 'Électricité']);
        if (!$category) {
            throw new \Exception('La catégorie "Électricité" n\'existe pas.');
        }

        foreach ($data['results'] as $item) {
            $date = new \DateTime($item['date']);

            $existing = $this->em->getRepository(ConsumptionRecord::class)->findOneBy([
                'date' => $date,
                'category' => $category,
                'user' => null,
            ]);

            if ($existing) {
                continue;
            }

            $record = new ConsumptionRecord();
            $record->setDate($date);
            $record->setValue($item['consommation_brute_electricite_rte'] ?? 0);
            $record->setCategory($category);
            $record->setUser(null); // ou un utilisateur par défaut

            $this->em->persist($record);
        }

        $this->em->flush();
    }
}
