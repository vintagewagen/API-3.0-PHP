<?php

namespace Cielo\Tests\Fixtures;

use Cielo\API30\Http\HttpClient;
use Cielo\API30\Http\HttpResponse;

/**
 * Grava as requisições e devolve respostas enfileiradas, sem rede.
 */
final class FakeHttpClient implements HttpClient
{
    /** @var list<array{method: string, url: string, headers: array<string, string>, body: ?string}> */
    public array $requests = [];

    /** @var list<HttpResponse|\RuntimeException> */
    private array $queue = [];

    public function respond(int $status, string $body = ''): self
    {
        $this->queue[] = new HttpResponse($status, $body);

        return $this;
    }

    public function fail(string $message): self
    {
        $this->queue[] = new \RuntimeException($message);

        return $this;
    }

    public function request(string $method, string $url, array $headers, ?string $body = null): HttpResponse
    {
        $this->requests[] = compact('method', 'url', 'headers', 'body');

        $next = array_shift($this->queue) ?? throw new \LogicException('Nenhuma resposta enfileirada');

        if ($next instanceof \RuntimeException) {
            throw $next;
        }

        return $next;
    }

    /** @return array{method: string, url: string, headers: array<string, string>, body: ?string} */
    public function last(): array
    {
        return $this->requests[array_key_last($this->requests)];
    }
}
