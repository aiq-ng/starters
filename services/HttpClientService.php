<?php

namespace Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpClientService
{
    private Client $client;
    private string $baseUrl;

    public function __construct(string $baseUrl = '')
    {
        $this->baseUrl = $baseUrl ?: getenv('WS_URL');
        $this->client = new Client();
    }

    public function post(string $endpoint, array $headers, string $body): ?array
    {
        try {
            $url = $this->baseUrl . $endpoint;

            $authToken = $this->getAuthTokenFromRequest();

            if ($authToken) {
                $headers['Authorization'] = "Bearer $authToken";
            }

            error_log("Sending HTTP request to $url");

            $response = $this->client->post($url, [
                'headers' => $headers,
                'body' => $body
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            error_log("Error sending HTTP request: " . $e->getMessage());
            return null;
        }
    }

    private function getAuthTokenFromRequest(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

        if ($authHeader && preg_match('/^Bearer\s+(.+)$/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
