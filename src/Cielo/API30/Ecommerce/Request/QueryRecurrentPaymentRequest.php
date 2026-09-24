<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Ecommerce\RecurrentPayment;
use Cielo\API30\Environment;
use Cielo\API30\Merchant;
use Cielo\API30\Http\HttpClient;
use Psr\Log\LoggerInterface;

/**
 * Class QueryRecurrentPaymentRequest
 *
 * @package Cielo\API30\Ecommerce\Request
 */
class QueryRecurrentPaymentRequest extends AbstractRequest
{

    private $environment;

	/**
	 * QueryRecurrentPaymentRequest constructor.
	 *
	 * @param Merchant $merchant
	 * @param Environment $environment
	 * @param LoggerInterface|null $logger
	 * @param HttpClient|null $httpClient
	 */
    public function __construct(Merchant $merchant, Environment $environment, ?LoggerInterface $logger = null, ?HttpClient $httpClient = null)
    {
        parent::__construct($merchant, $logger, $httpClient);

        $this->environment = $environment;
    }

    /**
     * @param $recurrentPaymentId
     *
     * @return null
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException
     * @throws \RuntimeException
     */
    public function execute($recurrentPaymentId)
    {
        $url = $this->environment->getApiQueryURL() . '1/RecurrentPayment/' . $recurrentPaymentId;

        return $this->sendRequest('GET', $url);
    }

    /**
     * @param $json
     *
     * @return RecurrentPayment
     */
    protected function unserialize($json)
    {
        return RecurrentPayment::fromJson($json);
    }
}
