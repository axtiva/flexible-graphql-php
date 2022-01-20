<?php

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation;

use Axtiva\FlexibleGraphql\Generator\Config\LanguageLevelConfigInterface;

/**
 * @property LanguageLevelConfigInterface $config
 */
trait GetPHPVersionFromCodeGeneratorTrait
{
    public function getPHPVersion(): string
    {
        return $this->config->getPHPVersion();
    }
}