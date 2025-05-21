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
    $url = 'https://odre.opendatasoft.com/api/records/1.0/search/?dataset=eco2mix-national-tr&rows=300';

    $response = $this->client->request('GET', $url);
    $data = $response->toArray();

    $records = $data['records'] ?? [];

    foreach ($records as $item) {
        $fields = $item['fields'] ?? null;

        if (!$fields || !isset($fields['date_heure'], $fields['consommation'], $fields['nature'])) {
            continue;
        }

        $datetime = new \DateTime($fields['date_heure']);
        $value = $fields['consommation'];
        $categoryName = ucfirst(trim($fields['nature']));

        
        $category = $this->em->getRepository(Category::class)->findOneBy(['name' => $categoryName]);
        if (!$category) {
            $category = new Category();
            $category->setName($categoryName);
            $this->em->persist($category);
        }

       
        $existing = $this->em->getRepository(ConsumptionRecord::class)->findOneBy([
            'date' => $datetime,
            'category' => $category,
            'user' => null,
        ]);

        if ($existing) {
            continue;
        }

        
        $record = new ConsumptionRecord();
        $record->setDate($datetime);
        $record->setValue($value);
        $record->setCategory($category);
        $record->setUser(null);
        $this->em->persist($record);
    }

    $this->em->flush();
}


}
