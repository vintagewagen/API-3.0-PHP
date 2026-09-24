# Changelog

## 2.0.0

Exige PHP 8.2. A API pública (classes, métodos e parâmetros) continua a mesma
da 1.4.x; quem está em PHP antigo continua recebendo a 1.4.x pelo Composer.

### Quebras

- PHP `^8.2` (antes `>=5.6`).
- `jsonSerialize()` dos modelos declara `: array`. Subclasses que sobrescrevem
  o método precisam declarar o mesmo tipo.
- O contexto dos logs de debug passou a ter chaves (`request`, `headers`,
  `body`, `status`) em vez de uma lista.
- 401, 403 e 5xx lançam `CieloRequestException` com mensagens próprias em vez
  de `Unknown status` (o código continua sendo o status HTTP).

### Segurança

- O log de debug gravava a `MerchantKey` e o CVV em texto puro. Agora
  `MerchantKey`, `SecurityCode`, `Cavv` e `Xid` são mascarados, `CardNumber`
  fica com 6+4 dígitos e `CardToken` com os 4 últimos, inclusive dentro de URLs.
- Parâmetros de path (`PaymentId`, `RecurrentPaymentId`, BIN) passam por
  `rawurlencode`; antes um valor com `/` ou `?` alterava o recurso chamado.
- cURL com timeout (30s, 10s para conectar), só HTTPS e sem seguir redirects.

### Novidades

- Pix pelo provider Cielo2: `Payment::pix()`, `getQrCodeBase64Image()`,
  `getQrCodeString()`, `getSentOrderId()`.
- `InitiatedTransactionIndicator` (CIT/MIT), obrigatório para Mastercard com
  credencial armazenada.
- `ExternalAuthentication` para 3DS 2.x, exigido em débito.
- `CreditCard::cardOnFile()` com `Usage` e `Reason`.
- Respostas: `IssuerTransactionId`, `PaymentAccountReference`,
  `MerchantAdviceCode`.
- Providers de boleto `Bradesco2` e `BancoDoBrasil3`.
- `HttpClient` injetável (último parâmetro de `CieloEcommerce`), com
  `CurlHttpClient` configurável como padrão.
- `RequestId` enviado como GUID, como a Cielo documenta.

### Correções

- `zeroAuth()` lançava `Error` em qualquer PHP 8 (`ZeroAuth::fromJson()` não
  era estático).
- Erro de cURL sem logger configurado causava fatal (`null->error()`).
- O SDK pedia `Accept-Encoding: gzip` sem descompactar a resposta; agora o
  cURL negocia a compressão e descompacta.
- `Browser::setBrowserFingerprint()` duplicava a chave no JSON.
- `ZeroAuth::setReturnCode()` não recebia o parâmetro.
- 400 sem lista de erros no corpo quebrava o tratamento da exceção.
- Remove deprecations do PHP 8.1–8.5 (nullable implícito, `curl_close()`,
  tipo de retorno de `jsonSerialize()`).
- `cancelSale()` documenta o retorno real (`Payment`).

### Depreciado

- `Payment::PROVIDER_BRADESCO` e `PROVIDER_BANCO_DO_BRASIL` (use `Bradesco2` e
  `BancoDoBrasil3`), `Payment::PAYMENTTYPE_ELECTRONIC_TRANSFER` e
  `CreditCard::HIPERCARD` (bandeira encerrada em 30/06/2025).

## 1.4.10

- Aceita `psr/log` 3.
