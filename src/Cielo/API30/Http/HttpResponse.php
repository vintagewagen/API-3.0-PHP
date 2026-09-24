<?php

namespace Cielo\API30\Http;

final class HttpResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body,
    ) {
    }
}
