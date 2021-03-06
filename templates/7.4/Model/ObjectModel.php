

declare (strict_types=1);
<?php if(isset($namespace)): ?>
namespace <?=$namespace?>;
<?php endif ?>

use Axtiva\FlexibleGraphql\Resolver\AutoGenerationInterface;
<?php foreach ($import_classes ?? [] as $import_class): ?>
use <?=$import_class?>;
<?php endforeach ?>

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * if you want to extend it or change, then remove interface AutoGenerationInterface
 * and it will be managed by you, not axtiva/flexible-graphql-php code generator
 * PHP representation of graphql type <?=$type_name?><?=PHP_EOL?>
<?php if(isset($type_description)): ?>
 * <?=$type_description?>
<?php endif ?>
 */
<?php if(count($implements)): ?>
final class <?=$short_class_name?> implements <?=implode(', ', $implements)?><?=PHP_EOL?>
<?php else: ?>
final class <?=$short_class_name?><?=PHP_EOL?>
<?php endif ?>
{
<?php foreach ($fields ?? [] as $field): ?>
<?php if($field['description'] || $field['deprecation'] || $field['type_doc']): ?>
    /**
<?php if($field['description']): ?>
     * <?=$field['description']?><?=PHP_EOL?>
<?php endif ?>
<?php if($field['deprecation']): ?>
     * @deprecation <?=$field['deprecation']?><?=PHP_EOL?>
<?php endif ?>
<?php if($field['type_doc']): ?>
     * @var <?=$field['type_doc']?><?php if ($field['is_list']): ?><?=str_repeat('[]', $field['list_level'] + (substr($field['type_doc'], -8) === 'iterable' ? -1 : 0))?><?php endif ?><?=PHP_EOL?>
<?php endif ?>
     */
<?php endif ?>
    public <?php if($field['type']): ?><?=$field['type']?> <?php endif ?>$<?=$field['name']?><?php if($field['is_nullable']): ?> = null<?php endif ?>;
<?php endforeach ?>
}