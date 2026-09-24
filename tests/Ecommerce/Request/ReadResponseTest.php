<?php

namespace Cielo\Tests\Ecommerce\Request;

use Cielo\API30\Ecommerce\Request\AbstractRequest;
use Cielo\API30\Ecommerce\Request\CieloRequestException;
use Cielo\API30\Merchant;
use Cielo\Tests\Fixtures\Responses;
use PHPUnit\Framework\TestCase;

final class ReadResponseTest extends TestCase
{
    private function request(): object
    {
        return new class(new Merchant('id', 'key')) extends AbstractRequest {
            public function execute($param)
            {
                return null;
            }

            protected function unserialize($json)
            {
                return json_decode($json, true);
            }

            public function read($status, $body)
            {
                return $this->readResponse($status, $body);
            }
        };
    }

    public function testSuccessStatusesAreUnserialized(): void
    {
        $this->assertSame(['ok' => true], $this->request()->read(200, '{"ok":true}'));
        $this->assertSame(['ok' => true], $this->request()->read(201, '{"ok":true}'));
    }

    public function testBadRequestChainsOneExceptionPerCieloError(): void
    {
        try {
            $this->request()->read(400, Responses::get('error-400'));
            $this->fail('Expected CieloRequestException');
        } catch (CieloRequestException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame(101, $e->getCieloError()->getCode());
            $this->assertSame('MerchantId is required', $e->getCieloError()->getMessage());

            $previous = $e->getPrevious();
            $this->assertInstanceOf(CieloRequestException::class, $previous);
            $this->assertSame(126, $previous->getCieloError()->getCode());
            $this->assertNull($previous->getPrevious());
        }
    }

    public function testNotFound(): void
    {
        $this->expectException(CieloRequestException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Resource not found');

        $this->request()->read(404, '');
    }

    public function testOtherStatusesAreUnknown(): void
    {
        $this->expectException(CieloRequestException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Unknown status');

        $this->request()->read(500, '');
    }
}
