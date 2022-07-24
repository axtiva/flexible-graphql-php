<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use Axtiva\FlexibleGraphql\Exception\RepresentationResolverDoesNotFound;
use Axtiva\FlexibleGraphql\Representation;
use Axtiva\FlexibleGraphql\Resolver\_EntitiesResolverInterface;
use Axtiva\FlexibleGraphql\Resolver\FederationRepresentationResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

class _EntitiesResolver implements _EntitiesResolverInterface
{
    /**
     * @var FederationRepresentationResolverInterface[]
     */
    private array $resolvers;

    public function __construct(FederationRepresentationResolverInterface ...$resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[$resolver->getTypeName()] = $resolver;
        }
    }

    public function __invoke($rootValue, $args, $context, ResolveInfo $info)
    {
        $result = [];
        foreach ($args['representations'] ?? [] as $representation) {
            $representation = new Representation($representation);
            if (empty($this->resolvers[$representation->getTypename()])) {
                throw new RepresentationResolverDoesNotFound($representation);
            }

            $result[] = $this->resolvers[$representation->getTypename()]->__invoke($representation, $context, $info);
        }

        return $result;
    }
}