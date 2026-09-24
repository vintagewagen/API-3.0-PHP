<?php

namespace Cielo\API30\Ecommerce;

/**
 * Class Payment
 *
 * @package Cielo\API30\Ecommerce
 */
class Payment implements \JsonSerializable
{

    const PAYMENTTYPE_CREDITCARD = 'CreditCard';

    const PAYMENTTYPE_DEBITCARD = 'DebitCard';

    /**
     * @deprecated não consta mais na documentação atual da Cielo.
     */
    const PAYMENTTYPE_ELECTRONIC_TRANSFER = 'ElectronicTransfer';

    const PAYMENTTYPE_BOLETO = 'Boleto';

    const PAYMENTTYPE_PIX = 'Pix';

    /**
     * @deprecated use PROVIDER_BRADESCO2
     */
    const PROVIDER_BRADESCO = 'Bradesco';

    /**
     * @deprecated use PROVIDER_BANCO_DO_BRASIL3
     */
    const PROVIDER_BANCO_DO_BRASIL = 'BancoDoBrasil';

    /** Provider atual de boleto Bradesco. */
    const PROVIDER_BRADESCO2 = 'Bradesco2';

    /** Provider atual de boleto Banco do Brasil. */
    const PROVIDER_BANCO_DO_BRASIL3 = 'BancoDoBrasil3';

    /** Provider de Pix para integrações novas (desde 01/09/2025). */
    const PROVIDER_CIELO2 = 'Cielo2';

    const PROVIDER_SIMULADO = 'Simulado';

    private $serviceTaxAmount;

    private $installments;

    private $interest;

    private $capture = false;

    private $authenticate = false;

    private $recurrent;

    private $recurrentPayment;

    private $creditCard;

    private $debitCard;

    private $authenticationUrl;

    private $tid;

    private $proofOfSale;

    private $authorizationCode;

    private $softDescriptor = "";

    private $returnUrl;

    private $provider;

    private $paymentId;

    private $type;

    private $amount;

    private $receivedDate;

    private $capturedAmount;

    private $capturedDate;

    private $voidedAmount;

    private $voidedDate;

    private $currency;

    private $country;

    private $returnCode;

    private $returnMessage;

    private $status;

    private $links;

    private $extraDataCollection;

    private $expirationDate;

    private $url;

    private $number;

    private $boletoNumber;

    private $barCodeNumber;

    private $digitableLine;

    private $address;

    private $assignor;

    private $demonstrative;

    private $identification;

    private $instructions;

    private $fraudAnalysis;

    // Campos abaixo só vão no payload quando preenchidos (ver jsonSerialize).

    private $initiatedTransactionIndicator;

    private $externalAuthentication;

    private $qrCode;

    private $qrCodeBase64Image;

    private $qrCodeString;

    private $sentOrderId;

    private $issuerTransactionId;

    private $paymentAccountReference;

    private $merchantAdviceCode;

    /** Campos adicionados na 2.0, omitidos do JSON quando nulos. */
    private const OPTIONAL_FIELDS = [
        'initiatedTransactionIndicator',
        'externalAuthentication',
        'qrCode',
        'qrCodeBase64Image',
        'qrCodeString',
        'sentOrderId',
        'issuerTransactionId',
        'paymentAccountReference',
        'merchantAdviceCode',
    ];
    /**
     * Payment constructor.
     *
     * @param int $amount
     * @param int $installments
     */
    public function __construct($amount = 0, $installments = 1)
    {
        $this->setAmount($amount);
        $this->setInstallments($installments);
    }

    /**
     * @param $json
     *
     * @return Payment
     */
    public static function fromJson($json)
    {
        $payment = new Payment();
        $payment->populate(json_decode($json));

        return $payment;
    }

    /**
     * @param \stdClass $data
     */
    public function populate(\stdClass $data)
    {

        $this->serviceTaxAmount = isset($data->ServiceTaxAmount) ? $data->ServiceTaxAmount : null;
        $this->installments     = isset($data->Installments) ? $data->Installments : null;
        $this->interest         = isset($data->Interest) ? $data->Interest : null;
        $this->capture          = isset($data->Capture) ? !!$data->Capture : false;
        $this->authenticate     = isset($data->Authenticate) ? !!$data->Authenticate : false;
        $this->recurrent        = isset($data->Recurrent) ? !!$data->Recurrent : false;

        if (isset($data->RecurrentPayment)) {
            $this->recurrentPayment = new RecurrentPayment(false);
            $this->recurrentPayment->populate($data->RecurrentPayment);
        }

        if (isset($data->CreditCard)) {
            $this->creditCard = new CreditCard();
            $this->creditCard->populate($data->CreditCard);
        }

        if (isset($data->DebitCard)) {
            $this->debitCard = new CreditCard();
            $this->debitCard->populate($data->DebitCard);
        }

        if (isset($data->FraudAnalysis)) {
            $this->fraudAnalysis = new FraudAnalysis();
            $this->fraudAnalysis->populate($data->FraudAnalysis);
        }

        $this->expirationDate = isset($data->ExpirationDate) ? $data->ExpirationDate : null;
        $this->url            = isset($data->Url) ? $data->Url : null;
        $this->boletoNumber   = isset($data->BoletoNumber) ? $data->BoletoNumber : null;
        $this->barCodeNumber  = isset($data->BarCodeNumber) ? $data->BarCodeNumber : null;
        $this->digitableLine  = isset($data->DigitableLine) ? $data->DigitableLine : null;
        $this->address        = isset($data->Address) ? $data->Address : null;

        $this->authenticationUrl = isset($data->AuthenticationUrl) ? $data->AuthenticationUrl : null;
        $this->tid               = isset($data->Tid) ? $data->Tid : null;
        $this->proofOfSale       = isset($data->ProofOfSale) ? $data->ProofOfSale : null;
        $this->authorizationCode = isset($data->AuthorizationCode) ? $data->AuthorizationCode : null;
        $this->softDescriptor    = isset($data->SoftDescriptor) ? $data->SoftDescriptor : null;
        $this->provider          = isset($data->Provider) ? $data->Provider : null;
        $this->paymentId         = isset($data->PaymentId) ? $data->PaymentId : null;
        $this->type              = isset($data->Type) ? $data->Type : null;
        $this->amount            = isset($data->Amount) ? $data->Amount : null;
        $this->receivedDate      = isset($data->ReceivedDate) ? $data->ReceivedDate : null;
        $this->capturedAmount    = isset($data->CapturedAmount) ? $data->CapturedAmount : null;
        $this->capturedDate      = isset($data->CapturedDate) ? $data->CapturedDate : null;
        $this->voidedAmount      = isset($data->VoidedAmount) ? $data->VoidedAmount : null;
        $this->voidedDate        = isset($data->VoidedDate) ? $data->VoidedDate : null;
        $this->currency          = isset($data->Currency) ? $data->Currency : null;
        $this->country           = isset($data->Country) ? $data->Country : null;
        $this->returnCode        = isset($data->ReturnCode) ? $data->ReturnCode : null;
        $this->returnMessage     = isset($data->ReturnMessage) ? $data->ReturnMessage : null;
        $this->status            = isset($data->Status) ? $data->Status : null;

        $this->links = isset($data->Links) ? $data->Links : [];

        $this->assignor       = isset($data->Assignor) ? $data->Assignor : null;
        $this->demonstrative  = isset($data->Demonstrative) ? $data->Demonstrative : null;
        $this->identification = isset($data->Identification) ? $data->Identification : null;
        $this->instructions   = isset($data->Instructions) ? $data->Instructions : null;

        if (isset($data->InitiatedTransactionIndicator)) {
            $this->initiatedTransactionIndicator = new InitiatedTransactionIndicator();
            $this->initiatedTransactionIndicator->populate($data->InitiatedTransactionIndicator);
        }

        if (isset($data->ExternalAuthentication)) {
            $this->externalAuthentication = new ExternalAuthentication();
            $this->externalAuthentication->populate($data->ExternalAuthentication);
        }

        $this->qrCode                  = isset($data->QrCode) ? (array) $data->QrCode : null;
        // O provider Pix antigo devolve "QrcodeBase64Image"; o Cielo2, "QrCodeBase64Image".
        $this->qrCodeBase64Image       = $data->QrCodeBase64Image ?? $data->QrcodeBase64Image ?? null;
        $this->qrCodeString            = $data->QrCodeString ?? null;
        $this->sentOrderId             = $data->SentOrderId ?? null;
        $this->issuerTransactionId     = $data->IssuerTransactionId ?? null;
        $this->paymentAccountReference = $data->PaymentAccountReference ?? null;
        $this->merchantAdviceCode      = $data->MerchantAdviceCode ?? null;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);

        foreach (self::OPTIONAL_FIELDS as $field) {
            if ($data[$field] === null) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Configura o pagamento como Pix pelo provider Cielo2.
     *
     * @param int|null $expiration validade do QR Code em segundos (padrão da Cielo: 86400)
     *
     * @return $this
     */
    public function pix($expiration = null)
    {
        $this->setType(self::PAYMENTTYPE_PIX);
        $this->setProvider(self::PROVIDER_CIELO2);
        $this->qrCode = $expiration === null ? null : ['Expiration' => $expiration];

        return $this;
    }

    /**
     * @param string $category    InitiatedTransactionIndicator::CATEGORY_*
     * @param string $subcategory InitiatedTransactionIndicator::SUBCATEGORY_*
     *
     * @return InitiatedTransactionIndicator
     */
    public function initiatedTransactionIndicator($category, $subcategory)
    {
        $this->initiatedTransactionIndicator = new InitiatedTransactionIndicator($category, $subcategory);

        return $this->initiatedTransactionIndicator;
    }

    /**
     * @return ExternalAuthentication
     */
    public function externalAuthentication()
    {
        $this->externalAuthentication = new ExternalAuthentication();

        return $this->externalAuthentication;
    }

    /**
     * @param $securityCode
     * @param $brand
     *
     * @return CreditCard
     */
    public function creditCard($securityCode, $brand)
    {
        $card = $this->newCard($securityCode, $brand);

        $this->setType(self::PAYMENTTYPE_CREDITCARD);
        $this->setCreditCard($card);

        return $card;
    }

    /**
     * @param $securityCode
     * @param $brand
     *
     * @return CreditCard
     */
    private function newCard($securityCode, $brand)
    {
        $card = new CreditCard();
        $card->setSecurityCode($securityCode);
        $card->setBrand($brand);

        return $card;
    }

    /**
     * @param $securityCode
     * @param $brand
     *
     * @return CreditCard
     */
    public function debitCard($securityCode, $brand)
    {
        $card = $this->newCard($securityCode, $brand);

        $this->setType(self::PAYMENTTYPE_DEBITCARD);
        $this->setDebitCard($card);

        return $card;
    }

    /**
     * @param bool $authorizeNow
     *
     * @return RecurrentPayment
     */
    public function recurrentPayment($authorizeNow = true)
    {
        $recurrentPayment = new RecurrentPayment($authorizeNow);

        $this->setRecurrentPayment($recurrentPayment);

        return $recurrentPayment;
    }

    /**
     * @return mixed
     */
    public function getServiceTaxAmount()
    {
        return $this->serviceTaxAmount;
    }

    /**
     * @param $serviceTaxAmount
     *
     * @return $this
     */
    public function setServiceTaxAmount($serviceTaxAmount)
    {
        $this->serviceTaxAmount = $serviceTaxAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param $installments
     *
     * @return $this
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * @param $interest
     *
     * @return $this
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * @param $capture
     *
     * @return $this
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAuthenticate()
    {
        return $this->authenticate;
    }

    /**
     * @param $authenticate
     *
     * @return $this
     */
    public function setAuthenticate($authenticate)
    {
        $this->authenticate = $authenticate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurrent()
    {
        return $this->recurrent;
    }

    /**
     * @param $recurrent
     *
     * @return $this
     */
    public function setRecurrent($recurrent)
    {
        $this->recurrent = $recurrent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurrentPayment()
    {
        return $this->recurrentPayment;
    }

    /**
     * @param $recurrentPayment
     *
     * @return $this
     */
    public function setRecurrentPayment($recurrentPayment)
    {
        $this->recurrentPayment = $recurrentPayment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @param CreditCard $creditCard
     *
     * @return $this
     */
    public function setCreditCard(CreditCard $creditCard)
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDebitCard()
    {
        return $this->debitCard;
    }

    /**
     * @param mixed $debitCard
     *
     * @return $this
     */
    public function setDebitCard($debitCard)
    {
        $this->debitCard = $debitCard;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthenticationUrl()
    {
        return $this->authenticationUrl;
    }

    /**
     * @param $authenticationUrl
     *
     * @return $this
     */
    public function setAuthenticationUrl($authenticationUrl)
    {
        $this->authenticationUrl = $authenticationUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTid()
    {
        return $this->tid;
    }

    /**
     * @param $tid
     *
     * @return $this
     */
    public function setTid($tid)
    {
        $this->tid = $tid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProofOfSale()
    {
        return $this->proofOfSale;
    }

    /**
     * @param $proofOfSale
     *
     * @return $this
     */
    public function setProofOfSale($proofOfSale)
    {
        $this->proofOfSale = $proofOfSale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * @param $authorizationCode
     *
     * @return $this
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor()
    {
        return $this->softDescriptor;
    }

    /**
     * @param $softDescriptor
     *
     * @return $this
     */
    public function setSoftDescriptor($softDescriptor)
    {
        $this->softDescriptor = $softDescriptor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param $returnUrl
     *
     * @return $this
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param $provider
     *
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param $paymentId
     *
     * @return $this
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * @param $receivedDate
     *
     * @return $this
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapturedAmount()
    {
        return $this->capturedAmount;
    }

    /**
     * @param $capturedAmount
     *
     * @return $this
     */
    public function setCapturedAmount($capturedAmount)
    {
        $this->capturedAmount = $capturedAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapturedDate()
    {
        return $this->capturedDate;
    }

    /**
     * @param $capturedDate
     *
     * @return $this
     */
    public function setCapturedDate($capturedDate)
    {
        $this->capturedDate = $capturedDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVoidedAmount()
    {
        return $this->voidedAmount;
    }

    /**
     * @param $voidedAmount
     *
     * @return $this
     */
    public function setVoidedAmount($voidedAmount)
    {
        $this->voidedAmount = $voidedAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVoidedDate()
    {
        return $this->voidedDate;
    }

    /**
     * @param $voidedDate
     *
     * @return $this
     */
    public function setVoidedDate($voidedDate)
    {
        $this->voidedDate = $voidedDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * @param $returnCode
     *
     * @return $this
     */
    public function setReturnCode($returnCode)
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnMessage()
    {
        return $this->returnMessage;
    }

    /**
     * @param $returnMessage
     *
     * @return $this
     */
    public function setReturnMessage($returnMessage)
    {
        $this->returnMessage = $returnMessage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param $links
     *
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraDataCollection()
    {
        return $this->extraDataCollection;
    }

    /**
     * @param $extraDataCollection
     *
     * @return $this
     */
    public function setExtraDataCollection($extraDataCollection)
    {
        $this->extraDataCollection = $extraDataCollection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param $expirationDate
     *
     * @return $this
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoletoNumber()
    {
        return $this->boletoNumber;
    }

    /**
     * @param $boletoNumber
     *
     * @return $this
     */
    public function setBoletoNumber($boletoNumber)
    {
        $this->boletoNumber = $boletoNumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBarCodeNumber()
    {
        return $this->barCodeNumber;
    }

    /**
     * @param $barCodeNumber
     *
     * @return $this
     */
    public function setBarCodeNumber($barCodeNumber)
    {
        $this->barCodeNumber = $barCodeNumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDigitableLine()
    {
        return $this->digitableLine;
    }

    /**
     * @param $digitableLine
     *
     * @return $this
     */
    public function setDigitableLine($digitableLine)
    {
        $this->digitableLine = $digitableLine;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAssignor()
    {
        return $this->assignor;
    }

    /**
     * @param $assignor
     *
     * @return $this
     */
    public function setAssignor($assignor)
    {
        $this->assignor = $assignor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDemonstrative()
    {
        return $this->demonstrative;
    }

    /**
     * @param $demonstrative
     *
     * @return $this
     */
    public function setDemonstrative($demonstrative)
    {
        $this->demonstrative = $demonstrative;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * @param $identification
     *
     * @return $this
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @param $instructions
     *
     * @return $this
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
    * @return mixed
    */
    public function getFraudAnalysis()
    {
        return $this->fraudAnalysis;
    }

    /**
    * @param $fraudAnalysis
    *
    * @return $this
    */
    public function setFraudAnalysis($fraudAnalysis)
    {
        $this->fraudAnalysis = $fraudAnalysis;
        return $this;
    }

    /**
     * @return InitiatedTransactionIndicator|null
     */
    public function getInitiatedTransactionIndicator()
    {
        return $this->initiatedTransactionIndicator;
    }

    /**
     * @return $this
     */
    public function setInitiatedTransactionIndicator(?InitiatedTransactionIndicator $initiatedTransactionIndicator)
    {
        $this->initiatedTransactionIndicator = $initiatedTransactionIndicator;

        return $this;
    }

    /**
     * @return ExternalAuthentication|null
     */
    public function getExternalAuthentication()
    {
        return $this->externalAuthentication;
    }

    /**
     * @return $this
     */
    public function setExternalAuthentication(?ExternalAuthentication $externalAuthentication)
    {
        $this->externalAuthentication = $externalAuthentication;

        return $this;
    }

    /**
     * @return int|null validade do QR Code Pix, em segundos
     */
    public function getQrCodeExpiration()
    {
        return $this->qrCode['Expiration'] ?? null;
    }

    /**
     * @return string|null imagem do QR Code Pix em base64
     */
    public function getQrCodeBase64Image()
    {
        return $this->qrCodeBase64Image;
    }

    /**
     * @return string|null código Pix copia e cola
     */
    public function getQrCodeString()
    {
        return $this->qrCodeString;
    }

    /**
     * @return string|null txid do Pix
     */
    public function getSentOrderId()
    {
        return $this->sentOrderId;
    }

    /**
     * @return string|null identificador da transação na bandeira
     */
    public function getIssuerTransactionId()
    {
        return $this->issuerTransactionId;
    }

    /**
     * @return string|null
     */
    public function getPaymentAccountReference()
    {
        return $this->paymentAccountReference;
    }

    /**
     * @return string|null código de retentativa da Mastercard
     */
    public function getMerchantAdviceCode()
    {
        return $this->merchantAdviceCode;
    }
}
