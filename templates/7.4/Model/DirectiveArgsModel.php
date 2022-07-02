

declare (strict_types=1);
<?php if (isset($namespace)): ?>
namespace <?=$namespace?>;
<?php endif ?>

use Axtiva\FlexibleGraphql\Type\InputType;
<?php foreach ($import_classes ?? [] as $import_class): ?>
use <?=$import_class?>;
<?php endforeach ?>

/**
 * This code is @generated by axtiva/flexible-graphql-php do not edit it
 * PHP representation of graphql directive args of <?=$type_name?><?=PHP_EOL?>
<?php if (isset($type_description)): ?>
 * <?=$type_description?>
<?php endif ?>
<?php foreach ($fields ?? [] as $field): ?>
 * @property <?php if (!empty($field['type_doc'])): ?><?=$field['type_doc']?><?php if ($field['is_list']): ?><?=str_repeat('[]', $field['list_level'] + (substr($field['type_doc'], -8) === 'iterable' ? -1 : 0))?><?php endif ?><?php else: ?>mixed<?php endif ?> $<?=$field['name']?><?php if ($field['is_nullable']): ?> = null<?php endif ?> <?=$field['description']?><?=PHP_EOL?>
<?php endforeach ?>
 */
<?php $listRender = function($result, $level, $varName = 'value') use (&$listRender) {
    if ($level === 0) {
        return $result;
    }

    return '(function($value) {foreach($value as $v) yield ' . $listRender($result, --$level, 'v') . ' })($' . $varName . ');';
}; ?>
final class <?=$short_class_name?> extends InputType
{
    protected function decorate($name, $value)
    {
        if ($value === null) {
            return null;
        }

<?php foreach ($fields ?? [] as $field): ?>
<?php if ($field['is_list'] && $field['is_custom'] && $field['type']): ?>
        if ($name === '<?=$field['name']?>') {
            return <?=$listRender('(function($value){ foreach($value as $v) yield ($v === null ? null : new ' . $field['type'] . '($v)); })($v);', $field['list_level'] - 1)?><?=PHP_EOL?>
        }

<?php elseif ($field['is_custom'] && $field['type']): ?>
        if ($name === '<?=$field['name']?>') {
            return new <?=$field['type']?>($value);
        }

<?php endif ?>
<?php endforeach ?>
        return $value;
    }
}