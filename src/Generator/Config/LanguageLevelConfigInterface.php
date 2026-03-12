<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

interface LanguageLevelConfigInterface
{
    /**
     * @deprecated use self::V8_3
     */
    public const V7_4 = self::V8_3;
    /**
     * @deprecated use self::V8_3
     */
    public const V8_0 = self::V8_3;
    /**
     * @deprecated use self::V8_3
     */
    public const V8_1 = self::V8_3;
    public const V8_3 = '8.3';

    public function getPHPVersion(): string;
}
