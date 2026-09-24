<?php

namespace Cielo\Tests\Ecommerce;

use Cielo\API30\Ecommerce\BinQuery;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\RecurrentPayment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\Tests\Fixtures\Responses;
use PHPUnit\Framework\TestCase;

final class ResponseParsingTest extends TestCase
{
    public function testParsesAuthorizedCreditCardSale(): void
    {
        $sale = Sale::fromJson(Responses::get('sale-credit-card-authorized'));

        $this->assertSame('123', $sale->getMerchantOrderId());
        $this->assertSame('Fulano de Tal', $sale->getCustomer()->getName());
        $this->assertSame('RJ', $sale->getCustomer()->getAddress()->getState());

        $payment = $sale->getPayment();
        $this->assertSame('24bc8366-fc31-4d6c-8555-17049a836a07', $payment->getPaymentId());
        $this->assertSame(Payment::PAYMENTTYPE_CREDITCARD, $payment->getType());
        $this->assertSame(2, $payment->getStatus());
        $this->assertSame(15700, $payment->getAmount());
        $this->assertSame(15700, $payment->getCapturedAmount());
        $this->assertSame(3, $payment->getInstallments());
        $this->assertSame('0924125303452', $payment->getTid());
        $this->assertSame('125303452', $payment->getProofOfSale());
        $this->assertSame('123456', $payment->getAuthorizationCode());
        $this->assertSame('6', $payment->getReturnCode());
        $this->assertTrue($payment->getCapture());
        $this->assertFalse($payment->getAuthenticate());
        $this->assertCount(2, $payment->getLinks());

        $card = $payment->getCreditCard();
        $this->assertInstanceOf(CreditCard::class, $card);
        $this->assertSame('455187******0183', $card->getCardNumber());
        $this->assertSame(CreditCard::VISA, $card->getBrand());
        $this->assertNull($payment->getDebitCard());
    }

    public function testParsesBoletoSale(): void
    {
        $payment = Sale::fromJson(Responses::get('sale-boleto'))->getPayment();

        $this->assertSame(Payment::PAYMENTTYPE_BOLETO, $payment->getType());
        $this->assertSame('00096629900000157000494250000000012300656560', $payment->getBarCodeNumber());
        $this->assertSame('00090.49420 50000.000013 23006.565602 6 62990000015700', $payment->getDigitableLine());
        $this->assertSame('123-2', $payment->getBoletoNumber());
        $this->assertStringStartsWith('https://', $payment->getUrl());
        $this->assertSame('2030-12-31', $payment->getExpirationDate());
    }

    public function testParsesRecurrentPayment(): void
    {
        $recurrent = RecurrentPayment::fromJson(Responses::get('recurrent-payment'));

        $this->assertSame('c30f5c78-fca2-459c-9f3c-9c4b41b09048', $recurrent->getRecurrentPaymentId());
        $this->assertSame(RecurrentPayment::INTERVAL_MONTHLY, $recurrent->getInterval());
        $this->assertSame('2026-10-24', $recurrent->getNextRecurrency());
        $this->assertSame(1, $recurrent->getStatus());
        $this->assertSame(24, $recurrent->getRecurrencyDay());
    }

    public function testParsesCardBin(): void
    {
        $bin = BinQuery::fromJson(Responses::get('card-bin'));

        $this->assertTrue($bin->getIsAccepted());
        $this->assertSame('00', $bin->getStatus());
        $this->assertSame('VISA', $bin->getProvider());
        $this->assertSame('Crédito', $bin->getCardType());
        $this->assertFalse($bin->getForeignCard());
        $this->assertSame('001', $bin->getIssuerCode());
    }

    public function testCardBinIsNotAcceptedWhenStatusIsNotZero(): void
    {
        $bin = new BinQuery();
        $bin->populate((object) ['Status' => '01']);

        $this->assertFalse($bin->getIsAccepted());
    }

    public function testParsesTokenizedCard(): void
    {
        $card = CreditCard::fromJson(Responses::get('tokenize-card'));

        $this->assertSame('db62dc71-d07b-4745-9969-42697b988ccb', $card->getCardToken());
    }

    public function testParsesCaptureResponseAsPayment(): void
    {
        $payment = Payment::fromJson(Responses::get('capture'));

        $this->assertSame(2, $payment->getStatus());
        $this->assertSame('6', $payment->getReturnCode());
        $this->assertSame('0924125303452', $payment->getTid());
    }
}
