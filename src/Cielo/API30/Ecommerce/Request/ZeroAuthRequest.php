<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\ZeroAuth;
use Cielo\API30\Environment;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

/**
 * Class ZeroAuthRequest
 *
 * @package Cielo\API30\Ecommerce\Request
 */
class ZeroAuthRequest extends AbstractRequest
{

    private $environment;

	/**
	 * ZeroAuthRequest constructor.
	 *
	 * @param Merchant $merchant
	 * @param Environment $environment
	 * @param LoggerInterface|null $logger
	 */
    public function __construct(Merchant $merchant, Environment $environment, LoggerInterface $logger = null)
    {
        parent::__construct($merchant, $logger);

        $this->environment = $environment;
    }

    /**
     * @param $creditCard
     *
     * @return null
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException
     * @throws \RuntimeException
     */
    public function execute($creditCard)
    {
        $url = $this->environment->getApiUrl() . '1/zeroauth';

        return $this->sendRequest('POST', $url, $creditCard);
    }

    /**
     * @param $json
     *
     * @return ZeroAuth
     */
    protected function unserialize($json)
    {
        return ZeroAuth::fromJson($json);
    }
}
