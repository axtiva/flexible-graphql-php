<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Wrapper;

use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class FieldResolverAmphpAsyncWrapped implements FieldResolverGeneratorInterface
{
    private FieldResolverGeneratorInterface $wrappedGenerator;

    public function __construct(FieldResolverGeneratorInterface $wrappedGenerator)
    {
        $this->wrappedGenerator = $wrappedGenerator;
    }

    public function hasResolver(Type $type, FieldDefinition $field): bool
    {
        return $this->wrappedGenerator->hasResolver($type, $field);
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        $wrappedResolver = $this->wrappedGenerator->generate($type, $field);
        return "(function(\$rootValue, \$args, \$context, \$info) {
                    return \Amp\async($wrappedResolver);
                })";
    }
}