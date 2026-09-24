<?php

namespace Cielo\API30\Http;

final class CurlHttpClient implements HttpClient
{
    /**
     * @param int $timeout        tempo máximo da requisição inteira, em segundos
     * @param int $connectTimeout tempo máximo para abrir a conexão, em segundos
     */
    public function __construct(
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 10,
    ) {
    }

    public function request(string $method, string $url, array $headers, ?string $body = null): HttpResponse
    {
        $curl = curl_init($url);

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        curl_setopt_array($curl, [
            CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_HTTPHEADER     => $headerLines,
        ]);

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \RuntimeException(sprintf('cURL error[%s]: %s', curl_errno($curl), curl_error($curl)));
        }

        return new HttpResponse((int) curl_getinfo($curl, CURLINFO_HTTP_CODE), (string) $response);
    }
}
