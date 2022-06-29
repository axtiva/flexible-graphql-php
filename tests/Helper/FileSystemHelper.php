<?php

namespace Axtiva\FlexibleGraphql\Tests\Helper;

class FileSystemHelper
{
    public static function mkdir($dir): bool
    {
        if (file_exists($dir)) {
            return true;
        }
        return mkdir($dir, 0777, true);
    }

    public static function rmdir($dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        foreach (array_diff(scandir($dir), ['.','..']) as $file) {
            (is_dir("$dir/$file")) ? self::rmdir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}