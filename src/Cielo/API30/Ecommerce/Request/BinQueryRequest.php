<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Ecommerce\BinQuery;
use Cielo\API30\Environment;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

/**
 * Class BinQueryRequest
 *
 * @package Cielo\API30\Ecommerce\Request
 */
class BinQueryRequest extends AbstractRequest
{

    private $environment;

	/**
	 * BinQueryRequest constructor.
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
     * @param $cardDigits
     *
     * @return null
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException
     * @throws \RuntimeException
     */
    public function execute($cardDigits)
    {
        $url = $this->environment->getApiQueryUrl() . '1/cardBin/'.$cardDigits;

        return $this->sendRequest('GET', $url);
    }

    /**
     * @param $json
     *
     * @return BinQuery
     */
    protected function unserialize($json)
    {
        return BinQuery::fromJson($json);
    }
}
