<?php
namespace core;

class Request
{
    private array $get;
    private array $post;
    private array $json;
    private array $files;
    private array $server;
    private array $routeParams = [];
    private ?int $authenticatedUserId = null;


    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;

        // If Content-Type is application/json, read data from php://input
        if ($this->isJson()) {
            $raw = file_get_contents("php://input");
            $this->json = json_decode($raw, true) ?? [];
        } else {
            $this->json = [];
        }
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->json[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->post, $this->json);
    }

    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? null;
    }

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function server(?string $key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->server;
        }
        return $this->server[$key] ?? $default;
    }

    public function isJson(): bool
    {
        return strpos($this->server['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function routeParam(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setAuthenticatedUserId(int $userId): void
    {
        $this->authenticatedUserId = $userId;
    }

    public function getAuthenticatedUserId(): ?int
    {
        return $this->authenticatedUserId;
    }

}
