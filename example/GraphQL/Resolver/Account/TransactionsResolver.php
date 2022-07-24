<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Account;

use Axtiva\FlexibleGraphql\Example\GraphQL\Model\AccountType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\TransactionType;
use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * This is resolver for Account.transactions
 */
final class TransactionsResolver implements ResolverInterface
{
    /**
     * @param AccountType $rootValue
     * @param $args
     * @param $context
     * @param ResolveInfo $info
     * @return TransactionType[]
     */
    public function __invoke($rootValue, $args, $context, ResolveInfo $info)
    {
        $transaction1 = new TransactionType();
        $transaction1->id = 'asdf';
        $transaction1->amount = 323;
        $transaction1->idStatus = 0;
        $transaction2 = new TransactionType();
        $transaction2->id = 'asdf';
        $transaction2->amount = 323;
        $transaction2->idStatus = 1;
        $transaction2->createdAt = new \DateTimeImmutable();

        return [
            $transaction1,
            $transaction2,
        ];
    }
}