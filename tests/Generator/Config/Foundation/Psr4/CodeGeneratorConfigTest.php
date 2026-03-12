<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use PHPUnit\Framework\TestCase;

class CodeGeneratorConfigTest extends TestCase
{
    public function testNotSupportedVersion(): void
    {
        $dir = uniqid('/tmp/TmpTestData/GraphQL');
        FileSystemHelper::mkdir($dir);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Not supported php version template 7.4 use only 8.3');

        try {
            new CodeGeneratorConfig($dir, '7.4', 'Axtiva\\FlexibleGraphql\\Example\\GraphQL');
        } finally {
            FileSystemHelper::rmdir($dir);
        }
    }
}
