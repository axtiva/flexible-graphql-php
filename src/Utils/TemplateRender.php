<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

class TemplateRender
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data): string
    {
        $content = static function () use ($template, $data): string {
            extract($data, EXTR_SKIP);

            ob_start();
            include $template;
            return (string) ob_get_clean();
        };

        $rendered = $content();
        if ($rendered === '') {
            return '<?php';
        }

        if (str_starts_with($rendered, '<?php')) {
            return $rendered;
        }

        return '<?php' . $rendered;
    }
}
