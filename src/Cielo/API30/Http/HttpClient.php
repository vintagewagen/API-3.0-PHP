<?php

namespace Cielo\API30\Http;

/**
 * Transporte HTTP usado pelas requisições do SDK.
 *
 * A implementação padrão é {@see CurlHttpClient}. Implemente esta interface
 * para usar outro cliente (Guzzle, Symfony HttpClient) ou para testes.
 */
interface HttpClient
{
    /**
     * @param string                $method  GET, POST ou PUT
     * @param string                $url
     * @param array<string, string> $headers nome => valor
     * @param string|null           $body    JSON já serializado
     *
     * @throws \RuntimeException em falha de transporte (DNS, timeout, TLS...)
     */
    public function request(string $method, string $url, array $headers, ?string $body = null): HttpResponse;
}
