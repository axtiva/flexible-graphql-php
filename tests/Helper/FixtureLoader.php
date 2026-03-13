<?php

namespace Axtiva\FlexibleGraphql\Tests\Helper;

final class FixtureLoader
{
    public static function load(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException('Unable to load fixture: ' . $path);
        }

        return self::normalizeLineEndings($content);
    }

    public static function normalizeLineEndings(string $content): string
    {
        return str_replace(["\r\n", "\r"], "\n", $content);
    }
}
