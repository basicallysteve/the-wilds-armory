<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class MonsterHunterWildsDatabase
{
    private $baseUrl;
    private $projectionFields = [ "id" => true, "name" => true];
    private $pageLimit = 100;
    private $pageOffset = 0;
    public function __construct()
    {
        $this->baseUrl = config('services.mhwdb.base_url');
    }

    private function projection(array $data)
    {
        $this->projectionFields = array_merge($this->projectionFields, $data);
    }

    public function limit(int $limit)
    {
        $this->pageLimit = $limit;
        return $this;
    }

    public function offset(int $offset)
    {
        $this->pageOffset = $offset;
        return $this;
    }

    public function getArmors(string $filters)
    {
        $cacheKey = "mhwdb.armors." . md5($filters) . ".limit={$this->pageLimit}.offset={$this->pageOffset}";
        $this->projection([
            "id" => true,
            "name" => true,
            "kind" => true,
            "defense" => true,
            "slots" => true,
            "resistances" => true,
            'skills' => true,
            "rank" => true,
            "rarity" => true,
        ]);

        $projectionFields = $this->projectionFields;
        return Cache::remember($cacheKey, 60 * 60 * 24 * 30, function () use ($filters, $projectionFields) {
            try {
                //json encode the projection fields
                $projection = json_encode($projectionFields);
                $client = new Client();
                $response = $client->request('GET', "{$this->baseUrl}/armor", [
                    'query' => [
                        'q' => $filters,
                        'p' => $projection
                    ],
                    'http_errors' => false,
                ]);
                if ($response->getStatusCode() === 200) {
                    return json_decode($response->getBody()->getContents(), true);
                }
            } catch (RequestException $e) {
                Log::error("Failed to fetch armors data: {$e->getMessage()}");
            }

            return [];
        });
    }

    public function getArmor(int $id)
    {
        $cacheKey = "mhwdb.armor.{$id}";
        $this->projection([
            "id" => true,
            "name" => true,
            "kind" => true,
            "defense" => true,
            "slots" => true,
            "resistances" => true,
        ]);

        $projectionFields = $this->projectionFields;

        // Cache for 1 month (60 seconds * 60 minutes * 24 hours * 30 days)
        return Cache::remember($cacheKey, 60 * 60 * 24 * 30, function () use ($id, $projectionFields) {
            try {
                //json encode the projection fields
                $projection = json_encode($projectionFields);
                $client = new Client();
                $response = $client->request('GET', "{$this->baseUrl}/armor/{$id}", [
                    'query' => ['p' => $projection],
                    'http_errors' => false,
                ]);
                if ($response->getStatusCode() === 200) {
                    return json_decode($response->getBody()->getContents(), true);
                }
            } catch (RequestException $e) {
                Log::error("Failed to fetch armor data for ID {$id}: {$e->getMessage()}");
            }

            return abort(404, "Armor with ID {$id} not found.");
        });
    }
}