<?php

namespace Cielo\Tests\Ecommerce;

use Cielo\API30\Ecommerce\BinQuery;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\RecurrentPayment;
use Cielo\API30\Ecommerce\Request\CieloRequestException;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\ZeroAuth;
use Cielo\API30\Merchant;
use Cielo\Tests\Fixtures\FakeHttpClient;
use Cielo\Tests\Fixtures\Responses;
use Cielo\Tests\Fixtures\SaleFactory;
use PHPUnit\Framework\TestCase;

final class CieloEcommerceTest extends TestCase
{
    private const API = 'https://apisandbox.cieloecommerce.cielo.com.br/';
    private const QUERY = 'https://apiquerysandbox.cieloecommerce.cielo.com.br/';
    private const PAYMENT_ID = '24bc8366-fc31-4d6c-8555-17049a836a07';

    private FakeHttpClient $http;
    private CieloEcommerce $cielo;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->cielo = new CieloEcommerce(new Merchant('merchant-id', 'merchant-key'), Environment::sandbox(), null, $this->http);
    }

    public function testCreateSale(): void
    {
        $this->http->respond(201, Responses::get('sale-credit-card-authorized'));

        $sale = $this->cielo->createSale(SaleFactory::creditCard());

        $request = $this->http->last();
        $this->assertSame('POST', $request['method']);
        $this->assertSame(self::API . '1/sales/', $request['url']);
        $this->assertSame('merchant-id', $request['headers']['MerchantId']);
        $this->assertSame('merchant-key', $request['headers']['MerchantKey']);
        $this->assertSame('application/json', $request['headers']['Content-Type']);
        $this->assertNotEmpty($request['headers']['RequestId']);
        $this->assertSame(json_encode(SaleFactory::creditCard()), $request['body']);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertSame(self::PAYMENT_ID, $sale->getPayment()->getPaymentId());
    }

    public function testGetSaleUsesQueryHost(): void
    {
        $this->http->respond(200, Responses::get('sale-credit-card-authorized'));

        $sale = $this->cielo->getSale(self::PAYMENT_ID);

        $request = $this->http->last();
        $this->assertSame('GET', $request['method']);
        $this->assertSame(self::QUERY . '1/sales/' . self::PAYMENT_ID, $request['url']);
        $this->assertNull($request['body']);
        $this->assertSame('0', $request['headers']['Content-Length']);
        $this->assertSame(2, $sale->getPayment()->getStatus());
    }

    public function testGetRecurrentPayment(): void
    {
        $this->http->respond(200, Responses::get('recurrent-payment'));

        $recurrent = $this->cielo->getRecurrentPayment('c30f5c78-fca2-459c-9f3c-9c4b41b09048');

        $this->assertSame(self::QUERY . '1/RecurrentPayment/c30f5c78-fca2-459c-9f3c-9c4b41b09048', $this->http->last()['url']);
        $this->assertInstanceOf(RecurrentPayment::class, $recurrent);
        $this->assertSame('2026-10-24', $recurrent->getNextRecurrency());
    }

    public function testCaptureSaleWithAmounts(): void
    {
        $this->http->respond(200, Responses::get('capture'));

        $payment = $this->cielo->captureSale(self::PAYMENT_ID, 15700, 1000);

        $request = $this->http->last();
        $this->assertSame('PUT', $request['method']);
        $this->assertSame(self::API . '1/sales/' . self::PAYMENT_ID . '/capture?amount=15700&serviceTaxAmount=1000', $request['url']);
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame(2, $payment->getStatus());
    }

    public function testCancelSalePartially(): void
    {
        $this->http->respond(200, Responses::get('capture'));

        $this->cielo->cancelSale(self::PAYMENT_ID, 5000);

        $request = $this->http->last();
        $this->assertSame('PUT', $request['method']);
        $this->assertSame(self::API . '1/sales/' . self::PAYMENT_ID . '/void?amount=5000', $request['url']);
    }

    public function testTokenizeCard(): void
    {
        $this->http->respond(201, Responses::get('tokenize-card'));

        $card = $this->cielo->tokenizeCard(SaleFactory::card());

        $request = $this->http->last();
        $this->assertSame('POST', $request['method']);
        $this->assertSame(self::API . '1/card/', $request['url']);
        $this->assertSame('db62dc71-d07b-4745-9969-42697b988ccb', $card->getCardToken());
    }

    public function testBinQuery(): void
    {
        $this->http->respond(200, Responses::get('card-bin'));

        $bin = $this->cielo->binQuery('455187');

        $this->assertSame('GET', $this->http->last()['method']);
        $this->assertSame(self::QUERY . '1/cardBin/455187', $this->http->last()['url']);
        $this->assertInstanceOf(BinQuery::class, $bin);
        $this->assertTrue($bin->getIsAccepted());
    }

    public function testZeroAuth(): void
    {
        $this->http->respond(200, Responses::get('zero-auth'));

        $result = $this->cielo->zeroAuth(SaleFactory::card()->setSecurityCode('123'));

        $this->assertSame('POST', $this->http->last()['method']);
        $this->assertSame(self::API . '1/zeroauth', $this->http->last()['url']);
        $this->assertInstanceOf(ZeroAuth::class, $result);
        $this->assertTrue($result->getValid());
    }

    public function testCieloErrorsBecomeExceptions(): void
    {
        $this->http->respond(400, Responses::get('error-400'));

        try {
            $this->cielo->createSale(SaleFactory::creditCard());
            $this->fail('Expected CieloRequestException');
        } catch (CieloRequestException $e) {
            $this->assertSame(101, $e->getCieloError()->getCode());
        }
    }

    public function testTransportErrorWithoutLoggerIsRethrown(): void
    {
        $this->http->fail('cURL error[28]: Operation timed out');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cURL error[28]: Operation timed out');

        $this->cielo->getSale(self::PAYMENT_ID);
    }

    public function testDefaultsToProductionAndCurl(): void
    {
        $cielo = new CieloEcommerce(new Merchant('id', 'key'));

        $environment = (new \ReflectionProperty($cielo, 'environment'))->getValue($cielo);
        $this->assertSame('https://api.cieloecommerce.cielo.com.br/', $environment->getApiUrl());
    }

    public function testCreditCardBrandConstantsUsedByTheReadme(): void
    {
        $this->assertSame('Visa', CreditCard::VISA);
        $this->assertSame('Master', CreditCard::MASTERCARD);
    }
}
