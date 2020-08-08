<?php

namespace Kfriars\ArrayToFile\Contracts;

interface FileContract
{
    /**
     * Save the contents to a file
     *
     * @param string $filepath
     * @param string $content
     * @return void
     * @throws FileSaveException
     */
    public function save($filepath, $content);
}
