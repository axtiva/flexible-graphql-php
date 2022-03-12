<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\FieldResolverProviderInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class WrappedContainerCallFieldResolverProvider implements FieldResolverProviderInterface
{
    private FieldResolverProviderInterface $generator;
    private ArgsFieldResolverGeneratorConfigInterface $argsFieldResolverGeneratorConfig;

    public function __construct(
        FieldResolverProviderInterface $generator,
        ArgsFieldResolverGeneratorConfigInterface $argsFieldResolverGeneratorConfig
    ) {
        $this->generator = $generator;
        $this->argsFieldResolverGeneratorConfig = $argsFieldResolverGeneratorConfig;
    }

    public function generate(FieldResolverGeneratorConfigInterface $config, Type $type, FieldDefinition $field): string
    {
        $argsDecorator = '';
        $argsClassName = $this->argsFieldResolverGeneratorConfig->getFieldArgsFullClassName($type, $field);
        if ($field->args && \class_exists($argsClassName)) {
            $argsDecorator = '$args = new \\' . ltrim($argsClassName, '\\') . '($args);';
        }

        return sprintf(<<<'PHP'
(function ($rootValue, $args, $context, $info) {
    %s
    return %s($rootValue, $args, $context, $info);
})
PHP, $argsDecorator, $this->generator->generate($config, $type, $field));
    }
}