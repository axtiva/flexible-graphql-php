<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Model;

use Axtiva\FlexibleGraphql\Generator\Exception\UnknownEnumValue;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * and will be regenerated. Do not edit it manually
 * PHP representation of graphql enum 
  * TRANSACTION STATUS DOC
  */
final class TransactionStatusEnum
{
    public const NEW = 'NEW';
    /**
     * SUCCESS DOC
     */
    public const SUCCESS = 'SUCCESS';
    public const FAIL = 'FAIL';
    public string $value;
    private static array $map = [
        self::NEW => true,
        self::SUCCESS => true,
        self::FAIL => true,
    ];

    public function __construct($value)
    {
        if (!isset(self::$map[$value])) {
            throw new UnknownEnumValue(__CLASS__, $value);
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}