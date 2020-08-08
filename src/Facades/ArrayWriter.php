<?php

namespace Kfriars\ArrayToFile\Facades;

use Kfriars\ArrayToFile\ArrayToFile;
use Kfriars\ArrayToFile\Exceptions\FileSaveException;

class ArrayWriter
{
    /**
     * Write the contents of an array to an includeable .php file
     *
     * @param array $array
     * @param string $filepath
     * @param mixed|null $transform
     * @return void
     * @throws FileSaveException
     */
    public static function toFile($array, $filename, $transform = null)
    {
        ArrayToFile::make()->write($array, $filename, $transform);
    }
}
