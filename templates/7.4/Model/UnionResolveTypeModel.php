

declare (strict_types=1);
<?php if(isset($namespace)): ?>
namespace <?=$namespace?>;
<?php endif ?>

use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\UnionResolveTypeInterface;
<?php foreach ($uses ?? [] as $use): ?>
use <?=$use?>;
<?php endforeach ?>

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * and will be regenerated. Do not edit it manually
<?php if(isset($description)): ?>
 * <?=$description?>
<?php endif ?>
 */
final class <?=$short_class_name?> implements UnionResolveTypeInterface
{
    public function __invoke($model, $context, ResolveInfo $info)
    {
        if (isset($model)) {
            switch (get_class($model)) {
<?php foreach ($models ?? [] as $model): ?>
                case <?=$model['model']?>::class:
                    return $info->schema->getType('<?=$model['type']?>');
<?php endforeach ?>
            }
        }
        return null;
    }
}