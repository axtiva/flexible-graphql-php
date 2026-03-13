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
        extract($data);
        ob_start();
        include($template);
        $content = ob_get_contents();
        ob_end_clean();
        if ($content === false) {
            return '<?php';
        }

        return '<?php' . $content;
    }
}
