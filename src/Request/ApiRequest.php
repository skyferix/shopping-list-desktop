<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiRequest
{
    private HttpClientInterface $client;
    private string $apiBaseUrl;
    private ResponseInterface $response;
    private array $headers;
    private mixed $content;
    private int $statusCode;
    private ?string $token;

    public function __construct(string $apiBaseUrl, HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->token = null;
    }

    public function login(string $relativeUrl, array $options = []): ApiRequest{
        try {
            $this->response = $this->client->request('POST', $this->apiBaseUrl . $relativeUrl, $options);
            $token = $this->response->getContent();
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            $this->statusCode = $e->getCode();
        }

        $this->token = json_decode($token??'')?->token;

        return $this;
    }

    public function request(string $token, string $method, string $relativeUrl, array $options = []): ApiRequest
    {
        $options['auth_bearer'] = $token;

        try {
            $this->response = $this->client->request($method, $this->apiBaseUrl . $relativeUrl, $options);
            $this->statusCode = $this->response->getStatusCode();
            $this->content = $this->response->getContent();
            $this->headers = $this->response->getHeaders();
        } catch (TransportExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|ClientExceptionInterface $e) {
            $this->statusCode = $e->getCode();
        }

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPureResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function generateErrorBasedOnStatusCode(int $statusCode): string
    {
        return match ($statusCode){
            400 => 'error.400',
            401 => 'error.401',
            460 => 'error.460',
            default => 'error.500',
        };
    }
}