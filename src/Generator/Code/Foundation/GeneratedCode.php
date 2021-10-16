<?php

namespace Axtiva\FlexibleGraphql\Generator\Code\Foundation;

class GeneratedCode
{
    private string $classname;
    private string $filename;

    public function __construct(string $classname, string $filename)
    {
        $this->classname = $classname;
        $this->filename = $filename;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getClassname(): string
    {
        return $this->classname;
    }
}