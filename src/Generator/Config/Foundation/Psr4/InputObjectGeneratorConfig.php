<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\GetPHPVersionFromCodeGeneratorTrait;
use Axtiva\FlexibleGraphql\Generator\Config\InputObjectGeneratorConfigInterface;
use GraphQL\Type\Definition\Type;

class InputObjectGeneratorConfig extends ObjectGeneratorConfig implements InputObjectGeneratorConfigInterface
{
    use GetPHPVersionFromCodeGeneratorTrait;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        parent::__construct($config);
    }

    public function getModelClassName(Type $type): string
    {
        return $type->toString() . 'InputType';
    }

    public function getModelNamespace(Type $type): ?string
    {
        return $this->config->getCodeNamespace() . '\\Model';
    }

    public function getModelDirPath(Type $type): string
    {
        return $this->config->getCodeDirPath() . \DIRECTORY_SEPARATOR . 'Model';
    }
}