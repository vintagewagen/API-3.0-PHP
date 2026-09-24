# API-3.0-PHP

[![CI](https://github.com/vintagewagen/API-3.0-PHP/actions/workflows/ci.yml/badge.svg)](https://github.com/vintagewagen/API-3.0-PHP/actions/workflows/ci.yml)

SDK PHP da API 3.0 do Cielo E-commerce. Fork mantido de `developercielo/api-3.0-php`.

## Principais recursos

* [x] Pagamentos por cartão de crédito.
* [x] Pagamentos recorrentes.
    * [x] Com autorização na primeira recorrência.
    * [x] Com autorização a partir da primeira recorrência.
* [x] Pagamentos por cartão de débito.
* [x] Pagamentos por boleto.
* [x] Pagamentos por Pix (provider Cielo2).
* [x] Autenticação 3DS 2.x externa (`ExternalAuthentication`).
* [x] Indicador de início da transação CIT/MIT (obrigatório para Mastercard com credencial armazenada).
* [x] Cancelamento de autorização.
* [x] Consulta de pagamentos.
* [x] Tokenização de cartão.

## Limitações

Por envolver a interface de usuário da aplicação, o SDK funciona apenas como um framework para criação das transações. Nos casos onde a autorização é direta, não há limitação; mas nos casos onde é necessário a autenticação ou qualquer tipo de redirecionamento do usuário, o desenvolvedor deverá utilizar o SDK para gerar o pagamento e, com o link retornado pela Cielo, providenciar o redirecionamento do usuário.

## Dependências

* PHP >= 8.2 (a série 1.4.x continua disponível para PHP antigo)
* Extensões `curl` e `json`
* `psr/log` 2 ou 3

## Instalando o SDK

Se já possui um arquivo `composer.json`, basta adicionar a seguinte dependência ao seu projeto:

```json
"require": {
    "vintagewagen/api-3.0-php": "^2.0"
}
```

Com a dependência adicionada ao `composer.json`, basta executar:

```
composer install
```

Alternativamente, você pode executar diretamente em seu terminal:

```
composer require "vintagewagen/api-3.0-php"
```

## Produtos e Bandeiras suportadas e suas constantes

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Ecommerce\CreditCard;
```

| Bandeira         | Constante              | Crédito à vista | Crédito parcelado Loja | Débito | Voucher |
|------------------|------------------------|-----------------|------------------------|--------|---------|
| Visa             | CreditCard::VISA       | Sim             | Sim                    | Sim    | *Não*   |
| Master Card      | CreditCard::MASTERCARD | Sim             | Sim                    | Sim    | *Não*   |
| American Express | CreditCard::AMEX       | Sim             | Sim                    | *Não*  | *Não*   |
| Elo              | CreditCard::ELO        | Sim             | Sim                    | *Não*  | *Não*   |
| Diners Club      | CreditCard::DINERS     | Sim             | Sim                    | *Não*  | *Não*   |
| Discover         | CreditCard::DISCOVER   | Sim             | *Não*                  | *Não*  | *Não*   |
| JCB              | CreditCard::JCB        | Sim             | Sim                    | *Não*  | *Não*   |
| Aura             | CreditCard::AURA       | Sim             | Sim                    | *Não*  | *Não*   |

Débito aceita Visa, Master e Elo, sempre com autenticação 3DS. A bandeira Hipercard
foi encerrada pela Cielo em 30/06/2025, e `CreditCard::HIPERCARD` está depreciada.

## Utilizando o SDK

Para criar um pagamento simples com cartão de crédito com o SDK, basta fazer:

### Criando um pagamento com cartão de crédito

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;

use Cielo\API30\Ecommerce\Request\CieloRequestException;
// ...
// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MERCHANT ID', 'MERCHANT KEY');

// Crie uma instância de Sale informando o ID do pedido na loja
$sale = new Sale('123');

// Crie uma instância de Customer informando o nome do cliente
$customer = $sale->customer('Fulano de Tal');

// Crie uma instância de Payment informando o valor do pagamento
$payment = $sale->payment(15700);

// Crie uma instância de Credit Card utilizando os dados de teste
// esses dados estão disponíveis no manual de integração
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal");

// Crie o pagamento na Cielo
try {
    // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
    $sale = (new CieloEcommerce($merchant, $environment))->createSale($sale);

    // Com a venda criada na Cielo, já temos o ID do pagamento, TID e demais
    // dados retornados pela Cielo
    $paymentId = $sale->getPayment()->getPaymentId();

    // Com o ID do pagamento, podemos fazer sua captura, se ela não tiver sido capturada ainda
    $sale = (new CieloEcommerce($merchant, $environment))->captureSale($paymentId, 15700, 0);

    // E também podemos fazer seu cancelamento, se for o caso
    $sale = (new CieloEcommerce($merchant, $environment))->cancelSale($paymentId, 15700);
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();
}
// ...
```

### Criando um pagamento e gerando o token do cartão de crédito

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;

use Cielo\API30\Ecommerce\Request\CieloRequestException;
// ...
// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MERCHANT ID', 'MERCHANT KEY');

// Crie uma instância de Sale informando o ID do pedido na loja
$sale = new Sale('123');

// Crie uma instância de Customer informando o nome do cliente
$customer = $sale->customer('Fulano de Tal');

// Crie uma instância de Payment informando o valor do pagamento
$payment = $sale->payment(15700);

// Crie uma instância de Credit Card utilizando os dados de teste
// esses dados estão disponíveis no manual de integração.
// Utilize setSaveCard(true) para obter o token do cartão
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal")
        ->setSaveCard(true);

// Crie o pagamento na Cielo
try {
    // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
    $sale = (new CieloEcommerce($merchant, $environment))->createSale($sale);

    // O token gerado pode ser armazenado em banco de dados para vendar futuras
    $token = $sale->getPayment()->getCreditCard()->getCardToken();
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();
}
// ...
```

### Criando um pagamento com cartão de crédito tokenizado

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;

use Cielo\API30\Ecommerce\Request\CieloRequestException;
// ...
// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MERCHANT ID', 'MERCHANT KEY');

// Crie uma instância de Sale informando o ID do pedido na loja
$sale = new Sale('123');

// Crie uma instância de Customer informando o nome do cliente
$customer = $sale->customer('Fulano de Tal');

// Crie uma instância de Payment informando o valor do pagamento
$payment = $sale->payment(15700);

// Crie uma instância de Credit Card utilizando os dados de teste
// esses dados estão disponíveis no manual de integração
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setCardToken("TOKEN-PREVIAMENTE-ARMAZENADO");

// Crie o pagamento na Cielo
try {
    // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
    $sale = (new CieloEcommerce($merchant, $environment))->createSale($sale);

    // Com a venda criada na Cielo, já temos o ID do pagamento, TID e demais
    // dados retornados pela Cielo
    $paymentId = $sale->getPayment()->getPaymentId();
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();
}
// ...
```

### Criando um pagamento recorrente

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;

use Cielo\API30\Ecommerce\Request\CieloRequestException;
// ...
// ...
// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MID', 'MKEY');

// Crie uma instância de Sale informando o ID do pedido na loja
$sale = new Sale('123');

// Crie uma instância de Customer informando o nome do cliente
$customer = $sale->customer('Fulano de Tal');

// Crie uma instância de Payment informando o valor do pagamento
$payment = $sale->payment(15700);

// Crie uma instância de Credit Card utilizando os dados de teste
// esses dados estão disponíveis no manual de integração
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal");

// Configure o pagamento recorrente
$payment->recurrentPayment(true)->setInterval(RecurrentPayment::INTERVAL_MONTHLY);

// Crie o pagamento na Cielo
try {
    // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
    $sale = (new CieloEcommerce($merchant, $environment))->createSale($sale);

    $recurrentPaymentId = $sale->getPayment()->getRecurrentPayment()->getRecurrentPaymentId();
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();
}
// ...
```

### Criando transações com cartão de débito

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\CreditCard;

use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...
// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MERCHANT ID', 'MERCHANT KEY');

// Crie uma instância de Sale informando o ID do pedido na loja
$sale = new Sale('123');

// Crie uma instância de Customer informando o nome do cliente
$customer = $sale->customer('Fulano de Tal');

// Crie uma instância de Payment informando o valor do pagamento
$payment = $sale->payment(15700);

// Débito exige autenticação 3DS. Informe o resultado obtido pelo script
// 3DS 2.x da Cielo (ou pelo seu MPI)
$payment->setAuthenticate(true)
        ->externalAuthentication()
        ->setCavv('AAABB2gHA1B5EFNjWQcDAAAAAAB=')
        ->setXid('Uk5ZanBHcWw2RDRkdGZxcGh0MjA=')
        ->setEci(5)
        ->setVersion('2.2.0')
        ->setReferenceId('a24a5d87-b1a1-4aef-a37b-2f30b91274e6');

// Crie uma instância de Debit Card utilizando os dados de teste
// esses dados estão disponíveis no manual de integração
$payment->debitCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2030")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal");

// Crie o pagamento na Cielo
try {
    // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
    $sale = (new CieloEcommerce($merchant, $environment))->createSale($sale);

    // Com a venda criada na Cielo, já temos o ID do pagamento, TID e demais
    // dados retornados pela Cielo
    $paymentId = $sale->getPayment()->getPaymentId();

    $status = $sale->getPayment()->getStatus();
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();
}
// ...
```

### Criando uma venda com Boleto

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;

use Cielo\API30\Ecommerce\Request\CieloRequestException;
// ...
// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MERCHANT ID', 'MERCHANT KEY');

// Crie uma instância de Sale informando o ID do pedido na loja
$sale = new Sale('123');

// Crie uma instância de Customer informando o nome do cliente,
// documento e seu endereço
$customer = $sale->customer('Fulano de Tal')
                  ->setIdentity('00000000001')
                  ->setIdentityType('CPF')
                  ->address()->setZipCode('22750012')
                             ->setCountry('BRA')
                             ->setState('RJ')
                             ->setCity('Rio de Janeiro')
                             ->setDistrict('Centro')
                             ->setStreet('Av Marechal Camara')
                             ->setNumber('123');

// Crie uma instância de Payment informando o valor do pagamento
$payment = $sale->payment(15700)
                ->setType(Payment::PAYMENTTYPE_BOLETO)
                ->setProvider(Payment::PROVIDER_BRADESCO2)
                ->setAddress('Rua de Teste')
                ->setBoletoNumber('1234')
                ->setAssignor('Empresa de Teste')
                ->setDemonstrative('Desmonstrative Teste')
                ->setExpirationDate(date('d/m/Y', strtotime('+1 month')))
                ->setIdentification('11884926754')
                ->setInstructions('Esse é um boleto de exemplo');

// Crie o pagamento na Cielo
try {
    // Configure o SDK com seu merchant e o ambiente apropriado para criar a venda
    $sale = (new CieloEcommerce($merchant, $environment))->createSale($sale);

    // Com a venda criada na Cielo, já temos o ID do pagamento, TID e demais
    // dados retornados pela Cielo
    $paymentId = $sale->getPayment()->getPaymentId();
    $boletoURL = $sale->getPayment()->getUrl();

    printf("URL Boleto: %s\n", $boletoURL);
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();
}
```

### Criando uma cobrança Pix

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;

use Cielo\API30\Ecommerce\Request\CieloRequestException;
// ...
$sale = new Sale('123');
$sale->customer('Fulano de Tal')
     ->setIdentity('00000000001')
     ->setIdentityType('CPF');

// Type Pix e provider Cielo2; o argumento opcional é a validade do QR Code em segundos
$sale->payment(15700)->pix(3600);

try {
    $sale = (new CieloEcommerce($merchant, Environment::sandbox()))->createSale($sale);

    $qrCodeImage = $sale->getPayment()->getQrCodeBase64Image(); // PNG em base64
    $copiaECola  = $sale->getPayment()->getQrCodeString();
    $txid        = $sale->getPayment()->getSentOrderId();
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Cartão armazenado e indicador CIT/MIT

Para Mastercard, transações com credencial armazenada (recorrência, card-on-file,
parcelamento do lojista) precisam do indicador de início da transação:

```php
<?php
use Cielo\API30\Ecommerce\CardOnFile;
use Cielo\API30\Ecommerce\InitiatedTransactionIndicator;

$payment->initiatedTransactionIndicator(
    InitiatedTransactionIndicator::CATEGORY_MERCHANT,        // M1
    InitiatedTransactionIndicator::SUBCATEGORY_SUBSCRIPTION
);

$payment->creditCard('123', CreditCard::MASTERCARD)
        ->setCardToken($token)
        ->cardOnFile(CardOnFile::USAGE_USED, CardOnFile::REASON_RECURRING);
```

### Tokenizando um cartão

```php
<?php

require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\CieloEcommerce;

use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...
// ...
// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MID', 'MKEY');

// Crie uma instância do objeto que irá retornar o token do cartão 
$card = new CreditCard();
$card->setCustomerName('Fulano de Tal');
$card->setCardNumber('0000000000000001');
$card->setHolder('Fulano de Tal');
$card->setExpirationDate('10/2020');
$card->setBrand(CreditCard::VISA);

try {
    // Configure o SDK com seu merchant e o ambiente apropriado para recuperar o cartão
    $card = (new CieloEcommerce($merchant, $environment))->tokenizeCard($card);

    // Get the token
    $cardToken = $card->getCardToken();
} catch (CieloRequestException $e) {
    // Em caso de erros de integração, podemos tratar o erro aqui.
    // os códigos de erro estão todos disponíveis no manual de integração.
    $error = $e->getCieloError();
}
// ...
```

## Tratamento de erros

Todas as operações lançam `CieloRequestException` com o status HTTP em `getCode()`:

| Status | Mensagem | Detalhes |
|--------|----------|----------|
| 400 | `Request Error` | `getCieloError()` traz `Code` e `Message` da Cielo; havendo vários erros, eles ficam encadeados em `getPrevious()` |
| 401 | `Unauthorized: ...` | MerchantId ou MerchantKey inválidos |
| 403 | `Forbidden: ...` | IP de origem fora da lista liberada na Cielo |
| 404 | `Resource not found` | |
| 5xx | `Cielo server error` | Falha do lado da Cielo, pode ser retentada com cuidado |

Falhas de transporte (DNS, timeout, TLS) lançam `\RuntimeException`.

## Logs, timeouts e cliente HTTP

O `CieloEcommerce` aceita um logger PSR-3 e um cliente HTTP opcionais:

```php
<?php
use Cielo\API30\Http\CurlHttpClient;

$cielo = new CieloEcommerce(
    $merchant,
    Environment::production(),
    $logger,                        // qualquer Psr\Log\LoggerInterface
    new CurlHttpClient(timeout: 20, connectTimeout: 5)
);
```

* Requisições e respostas são logadas em `debug`, com `MerchantKey`, `SecurityCode`, `Cavv` e `Xid`
  mascarados, `CardNumber` reduzido a 6+4 dígitos e `CardToken` aos 4 últimos.
* O `CurlHttpClient` padrão usa timeout de 30s (10s para conectar), aceita só HTTPS com TLS 1.2+
  e verificação de certificado, e não segue redirects.
* Para usar outro cliente (Guzzle, Symfony HttpClient) implemente `Cielo\API30\Http\HttpClient`.

## Desenvolvimento

```bash
composer install
composer check   # composer validate + PHPStan + PHPUnit
```

Os testes não acessam a rede: as requisições passam por um `HttpClient` falso, e o JSON enviado
à Cielo fica travado em snapshots (`tests/Fixtures/requests`). Para regenerá-los após uma mudança
intencional no payload, rode `UPDATE_SNAPSHOTS=1 vendor/bin/phpunit`.

## Manual

Documentação oficial da API 3.0: [docs.cielo.com.br](https://docs.cielo.com.br/ecommerce-cielo/reference)
