<?php

namespace Axtiva\FlexibleGraphql\Utils;

class TemplateRender
{
    public static function render(string $template, array $data): string
    {
        extract($data);
        ob_start();
        include($template);
        $content = ob_get_contents();
        ob_end_clean();
        return '<?php' . $content;
    }
}