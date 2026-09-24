<?php

namespace Cielo\API30\Ecommerce\Request;

/**
 * Remove segredos e dados de cartão antes de enviar headers e payloads ao logger.
 *
 * @internal
 */
final class LogSanitizer
{
    /** Chaves que nunca vão para o log, comparadas sem diferenciar maiúsculas. */
    private const SECRET_KEYS = ['merchantkey', 'securitycode', 'cavv', 'xid'];

    /** Chaves mascaradas mantendo início e fim para permitir correlação. */
    private const PARTIAL_KEYS = ['cardnumber' => [6, 4], 'cardtoken' => [0, 4]];

    private const MASK = '***';

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    public static function headers(array $headers): array
    {
        return self::redact($headers);
    }

    /**
     * Decodifica um JSON e mascara os campos sensíveis. Corpos que não são
     * JSON válido são descartados em vez de logados crus.
     */
    public static function json(?string $json): mixed
    {
        if ($json === null || $json === '') {
            return null;
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return sprintf('[corpo não-JSON com %d bytes omitido]', strlen($json));
        }

        return self::redact($data);
    }

    private static function redact(mixed $data): mixed
    {
        $replacements = [];
        $data = self::sanitize($data, $replacements);

        // O mesmo valor pode reaparecer em outro campo, como o CardToken no Href dos Links.
        return $replacements === [] ? $data : self::replaceIn($data, $replacements);
    }

    /**
     * @param array<string, string> $replacements valor original => valor mascarado
     */
    private static function sanitize(mixed $data, array &$replacements): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $normalized = strtolower((string) $key);

            if (in_array($normalized, self::SECRET_KEYS, true)) {
                $masked = $value === null ? null : self::MASK;
            } elseif (isset(self::PARTIAL_KEYS[$normalized]) && is_scalar($value)) {
                [$head, $tail] = self::PARTIAL_KEYS[$normalized];
                $masked = self::partial((string) $value, $head, $tail);
            } else {
                $data[$key] = self::sanitize($value, $replacements);
                continue;
            }

            if (is_scalar($value) && strlen((string) $value) >= 6) {
                $replacements[(string) $value] = (string) $masked;
            }

            $data[$key] = $masked;
        }

        return $data;
    }

    /**
     * @param array<string, string> $replacements
     */
    private static function replaceIn(mixed $data, array $replacements): mixed
    {
        if (is_string($data)) {
            return strtr($data, $replacements);
        }

        if (is_array($data)) {
            return array_map(fn ($value) => self::replaceIn($value, $replacements), $data);
        }

        return $data;
    }

    private static function partial(string $value, int $head, int $tail): string
    {
        $length = strlen($value);

        // Sem ao menos 4 caracteres escondidos, preservar as pontas exporia quase tudo.
        if ($length < $head + $tail + 4) {
            return self::MASK;
        }

        return substr($value, 0, $head) . str_repeat('*', $length - $head - $tail) . substr($value, -$tail);
    }
}
