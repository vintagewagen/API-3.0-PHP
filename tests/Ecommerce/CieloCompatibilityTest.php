<?php

namespace Cielo\Tests\Ecommerce;

use Cielo\API30\Ecommerce\CardOnFile;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\InitiatedTransactionIndicator;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Merchant;
use Cielo\Tests\Fixtures\FakeHttpClient;
use Cielo\Tests\Fixtures\Responses;
use Cielo\Tests\Fixtures\SaleFactory;
use PHPUnit\Framework\TestCase;

/**
 * Campos e fluxos da documentação atual da Cielo (docs.cielo.com.br).
 */
final class CieloCompatibilityTest extends TestCase
{
    private static function payload(\JsonSerializable $value): array
    {
        return json_decode(json_encode($value), true);
    }

    public function testRequestIdIsAGuid(): void
    {
        $http = (new FakeHttpClient())->respond(200, Responses::get('card-bin'))->respond(200, Responses::get('card-bin'));
        $cielo = new CieloEcommerce(new Merchant('id', 'key'), Environment::sandbox(), null, $http);

        $cielo->binQuery('455187');
        $cielo->binQuery('455187');

        [$first, $second] = array_column(array_column($http->requests, 'headers'), 'RequestId');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $first);
        $this->assertNotSame($first, $second);
    }

    public function testPixSaleWithCielo2(): void
    {
        $sale = new Sale('pix-1');
        $sale->customer('Aline de Souza')->setIdentity('12345678909')->setIdentityType('CPF');
        $sale->payment(100)->pix(3600);

        $payment = self::payload($sale)['payment'];
        $this->assertSame('Pix', $payment['type']);
        $this->assertSame('Cielo2', $payment['provider']);
        $this->assertSame(100, $payment['amount']);
        $this->assertSame(['Expiration' => 3600], $payment['qrCode']);
    }

    public function testPixWithoutExpirationOmitsQrCode(): void
    {
        $payment = (new Payment(100))->pix();

        $this->assertArrayNotHasKey('qrCode', self::payload($payment));
    }

    public function testParsesPixResponse(): void
    {
        $payment = Sale::fromJson(Responses::get('sale-pix'))->getPayment();

        $this->assertSame(Payment::PAYMENTTYPE_PIX, $payment->getType());
        $this->assertSame(12, $payment->getStatus());
        $this->assertSame('iVBORw0KGgoAAAANSUhEUg==', $payment->getQrCodeBase64Image());
        $this->assertStringStartsWith('00020101', $payment->getQrCodeString());
        $this->assertSame('a1b2c3d4e5f60718293a4b5c6d7e8f90', $payment->getSentOrderId());
        $this->assertSame(86400, $payment->getQrCodeExpiration());
    }

    public function testParsesLegacyPixImageField(): void
    {
        $payment = new Payment();
        $payment->populate((object) ['QrcodeBase64Image' => 'legacy==']);

        $this->assertSame('legacy==', $payment->getQrCodeBase64Image());
    }

    public function testInitiatedTransactionIndicatorAndCardOnFile(): void
    {
        $sale = SaleFactory::creditCard();
        $sale->getPayment()->initiatedTransactionIndicator(
            InitiatedTransactionIndicator::CATEGORY_MERCHANT,
            InitiatedTransactionIndicator::SUBCATEGORY_SUBSCRIPTION,
        );
        $sale->getPayment()->getCreditCard()->cardOnFile(CardOnFile::USAGE_USED, CardOnFile::REASON_RECURRING);

        $payment = self::payload($sale)['payment'];
        $this->assertSame(['category' => 'M1', 'subcategory' => 'Subscription'], $payment['initiatedTransactionIndicator']);
        $this->assertSame(['usage' => 'Used', 'reason' => 'Recurring'], $payment['creditCard']['cardOnFile']);
    }

    public function testCardOnFileFirstUseOmitsReason(): void
    {
        $card = (new CreditCard())->setCardNumber('4551870000000183');
        $card->cardOnFile(CardOnFile::USAGE_FIRST);

        $this->assertSame(['usage' => 'First'], self::payload($card)['cardOnFile']);
    }

    public function testExternalAuthenticationForDebit(): void
    {
        $sale = SaleFactory::debitCard();
        $sale->getPayment()->externalAuthentication()
            ->setCavv('AAABB2gHA1B5EFNjWQcDAAAAAAB=')
            ->setXid('Uk5ZanBHcWw2RDRkdGZxcGh0MjA=')
            ->setEci(5)
            ->setVersion('2.2.0')
            ->setReferenceId('a24a5d87-b1a1-4aef-a37b-2f30b91274e6');

        $this->assertSame([
            'cavv' => 'AAABB2gHA1B5EFNjWQcDAAAAAAB=',
            'xid' => 'Uk5ZanBHcWw2RDRkdGZxcGh0MjA=',
            'eci' => 5,
            'version' => '2.2.0',
            'referenceId' => 'a24a5d87-b1a1-4aef-a37b-2f30b91274e6',
        ], self::payload($sale)['payment']['externalAuthentication']);
    }

    public function testParsesNewResponseFields(): void
    {
        $payment = new Payment();
        $payment->populate((object) [
            'IssuerTransactionId' => '580027442382078',
            'PaymentAccountReference' => 'V0010013819231376366429200000',
            'MerchantAdviceCode' => '01',
            'InitiatedTransactionIndicator' => (object) ['Category' => 'C1', 'Subcategory' => 'CredentialsOnFile'],
            'CreditCard' => (object) ['CardOnFile' => (object) ['Usage' => 'First']],
        ]);

        $this->assertSame('580027442382078', $payment->getIssuerTransactionId());
        $this->assertSame('V0010013819231376366429200000', $payment->getPaymentAccountReference());
        $this->assertSame('01', $payment->getMerchantAdviceCode());
        $this->assertSame('C1', $payment->getInitiatedTransactionIndicator()->getCategory());
        $this->assertSame('First', $payment->getCreditCard()->getCardOnFile()->getUsage());
    }

    public function testCurrentBoletoProviders(): void
    {
        $this->assertSame('Bradesco2', Payment::PROVIDER_BRADESCO2);
        $this->assertSame('BancoDoBrasil3', Payment::PROVIDER_BANCO_DO_BRASIL3);
    }
}
