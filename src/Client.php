<?php

declare(strict_types=1);

namespace Skyeng\Codeception\Qase;

use GuzzleHttp\Client as HttpClient;

class Client
{
    private HttpClient $client;
    private string $project;
    private string $token;

    public function __construct(string $project, string $token)
    {
        $this->project = $project;
        $this->token = $token;

        $this->client =  new HttpClient([
            'base_uri' => 'https://api.qase.io',
        ]);
    }

    public function sendResults(array $results): void
    {
        $runId = $this->createRun();
        $this->bulkResults($runId, $results);
        $this->completeRun($runId);
    }

    private function createRun(): int
    {
        $r = $this->client->request('POST', "/v1/run/{$this->project}", [
            'headers' => $this->getHeaders(),
            'json' => ['title' => 'Test run ' . date('Y-m-d H:i:s')]
        ]);

        $decodedBody = json_decode($r->getBody()->getContents(), true);

        return $decodedBody['result']['id'];
    }

    private function bulkResults(int $runId, array $results): void
    {
        $this->client->request('POST', "/v1/result/{$this->project}/$runId/bulk", [
            'headers' => $this->getHeaders(),
            'json' => ['results' => $results]
        ]);
    }

    private function completeRun(int $runId): void
    {
        $this->client->request('POST', "/v1/run/{$this->project}/$runId/complete", [
            'headers' => $this->getHeaders(),
        ]);
    }

    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Token' => $this->token,
        ];
    }
}
