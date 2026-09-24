<?php

namespace Cielo\Tests\Ecommerce\Request;

use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Merchant;
use Cielo\Tests\Fixtures\FakeHttpClient;
use Cielo\Tests\Fixtures\Responses;
use Cielo\Tests\Fixtures\SaleFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

final class SecurityTest extends TestCase
{
    private const PAYMENT_ID = '24bc8366-fc31-4d6c-8555-17049a836a07';

    private FakeHttpClient $http;
    private object $logger;
    private CieloEcommerce $cielo;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->logger = new class extends AbstractLogger {
            /** @var list<array{level: mixed, message: string, context: array<mixed>}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = ['level' => $level, 'message' => (string) $message, 'context' => $context];
            }
        };
        $this->cielo = new CieloEcommerce(
            new Merchant('merchant-id', 'super-secret-merchant-key'),
            Environment::sandbox(),
            $this->logger,
            $this->http,
        );
    }

    private function logged(): string
    {
        return json_encode($this->logger->records, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function testRequestLogHidesMerchantKeyCvvAndFullCardNumber(): void
    {
        $this->http->respond(201, Responses::get('sale-credit-card-authorized'));

        $this->cielo->createSale(SaleFactory::creditCard());

        $log = $this->logged();
        $this->assertStringNotContainsString('super-secret-merchant-key', $log);
        $this->assertStringNotContainsString('4551870000000183', $log);
        $this->assertStringNotContainsString('"securityCode":"123"', $log);

        $request = $this->logger->records[0]['context'];
        $this->assertSame('POST https://apisandbox.cieloecommerce.cielo.com.br/1/sales/', $request['request']);
        $this->assertSame('merchant-id', $request['headers']['MerchantId']);
        $this->assertSame('***', $request['headers']['MerchantKey']);
        $this->assertSame('455187******0183', $request['body']['payment']['creditCard']['cardNumber']);
        $this->assertSame('***', $request['body']['payment']['creditCard']['securityCode']);
        $this->assertSame('Fulano de Tal', $request['body']['payment']['creditCard']['holder']);

        $response = $this->logger->records[1]['context'];
        $this->assertSame(201, $response['status']);
        $this->assertSame(self::PAYMENT_ID, $response['body']['Payment']['PaymentId']);
    }

    public function testResponseLogMasksCardToken(): void
    {
        $this->http->respond(201, Responses::get('tokenize-card'));

        $this->cielo->tokenizeCard(SaleFactory::card());

        $this->assertStringNotContainsString('db62dc71-d07b-4745-9969-42697b988ccb', json_encode($this->logger->records[1]));
        $this->assertStringEndsWith('8ccb', $this->logger->records[1]['context']['body']['CardToken']);
    }

    public function testNonJsonBodiesAreNotLoggedRaw(): void
    {
        $this->http->respond(502, '<html>Bad Gateway MerchantKey=super-secret-merchant-key</html>');

        try {
            $this->cielo->getSale(self::PAYMENT_ID);
        } catch (\Exception) {
        }

        $this->assertStringNotContainsString('super-secret-merchant-key', $this->logged());
    }

    public function testTransportErrorsAreLogged(): void
    {
        $this->http->fail('cURL error[28]: Operation timed out');

        try {
            $this->cielo->getSale(self::PAYMENT_ID);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertSame('error', $this->logger->records[1]['level']);
            $this->assertSame('cURL error[28]: Operation timed out', $this->logger->records[1]['message']);
        }
    }

    public function testPathParametersAreEncoded(): void
    {
        $this->http->respond(200, Responses::get('sale-credit-card-authorized'))
            ->respond(200, Responses::get('capture'))
            ->respond(200, Responses::get('card-bin'))
            ->respond(200, Responses::get('recurrent-payment'));

        $this->cielo->getSale('../card/x?y=1');
        $this->cielo->cancelSale('abc/capture');
        $this->cielo->binQuery('455187#');
        $this->cielo->getRecurrentPayment('a b');

        $urls = array_column($this->http->requests, 'url');
        $this->assertSame('https://apiquerysandbox.cieloecommerce.cielo.com.br/1/sales/..%2Fcard%2Fx%3Fy%3D1', $urls[0]);
        $this->assertSame('https://apisandbox.cieloecommerce.cielo.com.br/1/sales/abc%2Fcapture/void', $urls[1]);
        $this->assertSame('https://apiquerysandbox.cieloecommerce.cielo.com.br/1/cardBin/455187%23', $urls[2]);
        $this->assertSame('https://apiquerysandbox.cieloecommerce.cielo.com.br/1/RecurrentPayment/a%20b', $urls[3]);
    }
}
