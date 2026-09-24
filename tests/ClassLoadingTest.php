<?php

namespace Cielo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Carrega todas as classes do SDK. Com failOnDeprecation ligado, qualquer
 * deprecation de linguagem na declaração das classes quebra o teste.
 */
final class ClassLoadingTest extends TestCase
{
    public function testEverySourceFileDeclaresAnAutoloadableType(): void
    {
        $src = dirname(__DIR__) . '/src';
        $files = new \RegexIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src)),
            '/\.php$/'
        );

        $count = 0;
        foreach ($files as $file) {
            $type = str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($src) + 1));

            $this->assertTrue(
                class_exists($type) || interface_exists($type) || trait_exists($type),
                "{$type} não pôde ser carregado"
            );
            $count++;
        }

        $this->assertGreaterThan(30, $count);
    }
}
