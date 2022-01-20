<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

use GraphQL\Type\Definition\Type;

interface ModelGeneratorConfigInterface extends LanguageLevelConfigInterface
{
    public function getModelNamespace(Type $type): ?string;
    public function getModelClassName(Type $type): string;
    public function getModelFullClassName(Type $type): string;
    public function getModelClassFileName(Type $type): string;
    public function getModelDirPath(Type $type): string;
}