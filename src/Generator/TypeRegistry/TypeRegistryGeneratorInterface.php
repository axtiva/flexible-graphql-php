<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use Axtiva\FlexibleGraphql\Generator\Config\TypeRegistryGeneratorConfigInterface;
use GraphQL\Type\Schema;

interface TypeRegistryGeneratorInterface
{
    public function getConfig(): TypeRegistryGeneratorConfigInterface;
    public function generate(Schema $schema): string;
}