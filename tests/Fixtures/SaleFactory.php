<?php

namespace Cielo\Tests\Fixtures;

use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\RecurrentPayment;
use Cielo\API30\Ecommerce\Sale;

/**
 * Monta as vendas de exemplo usadas nos testes, seguindo o README.
 */
final class SaleFactory
{
    public static function creditCard(): Sale
    {
        $sale = new Sale('123');

        $customer = $sale->customer('Fulano de Tal');
        $customer->setEmail('fulano@example.com')
            ->setIdentity('12345678909')
            ->setIdentityType('CPF');
        $customer->address()
            ->setStreet('Rua Teste')
            ->setNumber('123')
            ->setZipCode('12345987')
            ->setCity('Rio de Janeiro')
            ->setState('RJ')
            ->setCountry('BRA');

        $payment = $sale->payment(15700, 3);
        $payment->setCapture(true)->setSoftDescriptor('LOJA TESTE');
        $payment->creditCard('123', CreditCard::VISA)
            ->setExpirationDate('12/2030')
            ->setCardNumber('4551870000000183')
            ->setHolder('Fulano de Tal');

        return $sale;
    }

    public static function debitCard(): Sale
    {
        $sale = new Sale('456');
        $sale->customer('Fulano de Tal');

        $payment = $sale->payment(15700);
        $payment->setReturnUrl('https://localhost/retorno')->setAuthenticate(true);
        $payment->debitCard('123', CreditCard::MASTERCARD)
            ->setExpirationDate('12/2030')
            ->setCardNumber('5555666677778884')
            ->setHolder('Fulano de Tal');

        return $sale;
    }

    public static function boleto(): Sale
    {
        $sale = new Sale('789');
        $sale->customer('Fulano de Tal')
            ->setIdentity('12345678909')
            ->address()
            ->setStreet('Rua Teste')
            ->setNumber('123')
            ->setZipCode('12345987')
            ->setCity('Rio de Janeiro')
            ->setState('RJ')
            ->setCountry('BRA')
            ->setDistrict('Centro');

        $sale->payment(15700)
            ->setType(Payment::PAYMENTTYPE_BOLETO)
            ->setProvider(Payment::PROVIDER_BRADESCO)
            ->setAddress('Rua de Teste')
            ->setBoletoNumber('1234')
            ->setAssignor('Empresa de Teste')
            ->setDemonstrative('Desmonstrative Teste')
            ->setExpirationDate('2030-12-31')
            ->setIdentification('11884926754')
            ->setInstructions('Esse é um boleto de exemplo');

        return $sale;
    }

    public static function recurrent(): Sale
    {
        $sale = new Sale('321');
        $sale->customer('Fulano de Tal');

        $payment = $sale->payment(15700);
        $payment->recurrentPayment(true)->setInterval(RecurrentPayment::INTERVAL_MONTHLY);
        $payment->creditCard('123', CreditCard::VISA)
            ->setExpirationDate('12/2030')
            ->setCardNumber('4551870000000183')
            ->setHolder('Fulano de Tal');

        return $sale;
    }

    public static function card(): CreditCard
    {
        return (new CreditCard())
            ->setCustomerName('Fulano de Tal')
            ->setCardNumber('4551870000000183')
            ->setHolder('Fulano de Tal')
            ->setExpirationDate('12/2030')
            ->setBrand(CreditCard::VISA);
    }
}
