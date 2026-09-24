<?php

namespace Cielo\Tests\Http;

use Cielo\API30\Http\CurlHttpClient;
use PHPUnit\Framework\TestCase;

/**
 * Só exercita falhas locais: nenhum teste sai da máquina.
 */
final class CurlHttpClientTest extends TestCase
{
    public function testConnectionFailureBecomesRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/^cURL error\[7\]/');

        (new CurlHttpClient(2, 1))->request('GET', 'https://127.0.0.1:1/', []);
    }

    public function testPlainHttpIsRefused(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/^cURL error\[1\]/');

        (new CurlHttpClient(2, 1))->request('GET', 'http://127.0.0.1:1/', []);
    }
}
