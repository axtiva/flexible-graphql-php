<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use Axtiva\FlexibleGraphql\Generator\Config\ModelGeneratorConfigInterface;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

interface ModelGeneratorInterface
{
    public function getConfig(): ModelGeneratorConfigInterface;
    public function isSupportedType(Type $type): bool;
    public function generate(Type $type, Schema $schema): string;
}