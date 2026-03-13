<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use Axtiva\FlexibleGraphql\Exception\RepresentationResolverDoesNotFound;
use Axtiva\FlexibleGraphql\Representation;
use Axtiva\FlexibleGraphql\Resolver\_EntitiesResolverInterface;
use Axtiva\FlexibleGraphql\Resolver\FederationRepresentationResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;
use ArrayAccess;

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

    public function __invoke(mixed $rootValue, array|ArrayAccess|null $args, mixed $context, ResolveInfo $info): mixed
    {
        $result = [];
        $representations = [];
        if (is_array($args) && isset($args['representations']) && is_iterable($args['representations'])) {
            $representations = $args['representations'];
        }

        foreach ($representations as $representation) {
            if (!is_array($representation)) {
                continue;
            }

            /** @var array<string, mixed> $representationData */
            $representationData = $representation;
            $representationObject = new Representation($representationData);
            if (empty($this->resolvers[$representationObject->getTypename()])) {
                throw new RepresentationResolverDoesNotFound($representationObject);
            }

            $result[] = $this->resolvers[$representationObject->getTypename()]->__invoke($representationObject, $context, $info);
        }

        return $result;
    }
}
