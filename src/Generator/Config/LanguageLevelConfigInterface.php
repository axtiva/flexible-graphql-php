<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

interface LanguageLevelConfigInterface
{
    public const V7_4 = '7.4';
    public const V8_0 = '8.0';
    public const V8_1 = '8.1';

    public function getPHPVersion(): string;
}