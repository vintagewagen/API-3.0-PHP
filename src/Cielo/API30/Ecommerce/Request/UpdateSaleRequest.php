<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Environment;
use Cielo\API30\Merchant;
use Cielo\API30\Http\HttpClient;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateSaleRequest
 *
 * @package Cielo\API30\Ecommerce\Request
 */
class UpdateSaleRequest extends AbstractRequest
{

    private $environment;

    private $type;

    private $serviceTaxAmount;

    private $amount;

	/**
	 * UpdateSaleRequest constructor.
	 *
	 * @param Merchant $type
	 * @param Merchant $merchant
	 * @param Environment $environment
	 * @param LoggerInterface|null $logger
	 * @param HttpClient|null $httpClient
	 */
    public function __construct($type, Merchant $merchant, Environment $environment, ?LoggerInterface $logger = null, ?HttpClient $httpClient = null)
    {
        parent::__construct($merchant, $logger, $httpClient);

        $this->environment = $environment;
        $this->type        = $type;
    }

    /**
     * @param $paymentId
     *
     * @return null
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException
     * @throws \RuntimeException
     */
    public function execute($paymentId)
    {
        $url    = $this->environment->getApiUrl() . '1/sales/' . rawurlencode((string) $paymentId) . '/' . rawurlencode((string) $this->type);
        $params = [];

        if ($this->amount != null) {
            $params['amount'] = $this->amount;
        }

        if ($this->serviceTaxAmount != null) {
            $params['serviceTaxAmount'] = $this->serviceTaxAmount;
        }

        if ($params !== []) {
            $url .= '?' . http_build_query($params);
        }

        return $this->sendRequest('PUT', $url);
    }

    /**
     * @param $json
     *
     * @return Payment
     */
    protected function unserialize($json)
    {
        return Payment::fromJson($json);
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
}
