<?php

namespace Cielo\Tests\Ecommerce;

use Cielo\Tests\Fixtures\SaleFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Trava o JSON que o SDK envia para a Cielo. Qualquer mudança no formato
 * enviado tem que ser intencional e atualizar o snapshot.
 *
 * Para regenerar: UPDATE_SNAPSHOTS=1 vendor/bin/phpunit
 */
final class RequestPayloadTest extends TestCase
{
    public static function payloads(): iterable
    {
        yield 'credit card sale' => ['sale-credit-card', SaleFactory::creditCard(...)];
        yield 'debit card sale' => ['sale-debit-card', SaleFactory::debitCard(...)];
        yield 'boleto sale' => ['sale-boleto', SaleFactory::boleto(...)];
        yield 'recurrent sale' => ['sale-recurrent', SaleFactory::recurrent(...)];
        yield 'tokenize card' => ['card', SaleFactory::card(...)];
    }

    #[DataProvider('payloads')]
    public function testPayloadMatchesSnapshot(string $name, \Closure $factory): void
    {
        $json = json_encode($factory(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        $file = __DIR__ . "/../Fixtures/requests/{$name}.json";

        if (getenv('UPDATE_SNAPSHOTS')) {
            file_put_contents($file, $json);
        }

        $this->assertFileExists($file);
        $this->assertSame(file_get_contents($file), $json);
    }
}
