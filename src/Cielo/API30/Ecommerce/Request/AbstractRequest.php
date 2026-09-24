<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Http\CurlHttpClient;
use Cielo\API30\Http\HttpClient;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractSaleRequest
 *
 * @package Cielo\API30\Ecommerce\Request
 */
abstract class AbstractRequest
{

    private $merchant;
    private $logger;
    private $httpClient;

    /**
     * AbstractSaleRequest constructor.
     *
     * @param Merchant $merchant
     * @param LoggerInterface|null $logger
     * @param HttpClient|null $httpClient transporte HTTP; o padrão é {@see CurlHttpClient}
     */
    public function __construct(Merchant $merchant, ?LoggerInterface $logger = null, ?HttpClient $httpClient = null)
    {
        $this->merchant   = $merchant;
        $this->logger     = $logger;
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    public abstract function execute($param);

    /**
     * @param                        $method
     * @param                        $url
     * @param \JsonSerializable|null $content
     *
     * @return mixed
     *
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException
     * @throws \RuntimeException
     */
    protected function sendRequest($method, $url, ?\JsonSerializable $content = null)
    {
        $headers = [
            'Accept'      => 'application/json',
            'User-Agent'  => 'CieloEcommerce/3.0 PHP SDK',
            'MerchantId'  => $this->merchant->getId(),
            'MerchantKey' => $this->merchant->getKey(),
            'RequestId'   => uniqid(),
        ];

        $body = null;

        if ($content !== null) {
            $body = json_encode($content);

            $headers['Content-Type'] = 'application/json';
        } else {
            $headers['Content-Length'] = '0';
        }

        if ($this->logger !== null) {
            $this->logger->debug('Requisição', [
                'request' => sprintf('%s %s', $method, $url),
                'headers' => LogSanitizer::headers($headers),
                'body'    => LogSanitizer::json($body),
            ]);
        }

        try {
            $response = $this->httpClient->request($method, $url, $headers, $body);
        } catch (\RuntimeException $e) {
            if ($this->logger !== null) {
                $this->logger->error($e->getMessage());
            }

            throw $e;
        }

        if ($this->logger !== null) {
            $this->logger->debug('Resposta', [
                'status' => $response->statusCode,
                'body'   => LogSanitizer::json($response->body),
            ]);
        }

        return $this->readResponse($response->statusCode, $response->body);
    }

    /**
     * @param $statusCode
     * @param $responseBody
     *
     * @return mixed
     *
     * @throws CieloRequestException
     */
    protected function readResponse($statusCode, $responseBody)
    {
        $unserialized = null;

        switch ($statusCode) {
            case 200:
            case 201:
                $unserialized = $this->unserialize($responseBody);
                break;
            case 400:
                $exception = null;
                $response  = json_decode($responseBody);

                foreach ($response as $error) {
                    $cieloError = new CieloError($error->Message, $error->Code);
                    $exception  = new CieloRequestException('Request Error', $statusCode, $exception);
                    $exception->setCieloError($cieloError);
                }

                throw $exception;
            case 404:
                throw new CieloRequestException('Resource not found', 404, null);
            default:
                throw new CieloRequestException('Unknown status', $statusCode);
        }

        return $unserialized;
    }

    /**
     * @param $json
     *
     * @return mixed
     */
    protected abstract function unserialize($json);
}
