<?php

namespace Cielo\Tests\Ecommerce;

use Cielo\API30\Ecommerce\Browser;
use Cielo\API30\Ecommerce\ZeroAuth;
use Cielo\Tests\Fixtures\Responses;
use PHPUnit\Framework\TestCase;

final class ModelRegressionTest extends TestCase
{
    public function testZeroAuthCanBeParsedStatically(): void
    {
        $zeroAuth = ZeroAuth::fromJson(Responses::get('zero-auth'));

        $this->assertTrue($zeroAuth->getValid());
        $this->assertSame('00', $zeroAuth->getReturnCode());
        $this->assertSame('Transacao autorizada', $zeroAuth->getReturnMessage());
        $this->assertSame('580027442382078', $zeroAuth->getIssuerTransactionId());
    }

    public function testZeroAuthSetReturnCode(): void
    {
        $this->assertSame('57', (new ZeroAuth())->setReturnCode('57')->getReturnCode());
    }

    public function testBrowserFingerprintIsSerializedOnce(): void
    {
        $browser = (new Browser())->setBrowserFingerprint('abc123');

        $this->assertSame('abc123', $browser->getBrowserFingerprint());

        $json = $browser->jsonSerialize();
        $this->assertSame('abc123', $json['browserFingerPrint']);
        $this->assertCount(1, array_filter(array_keys($json), fn ($k) => strcasecmp($k, 'browserFingerprint') === 0));
    }
}
