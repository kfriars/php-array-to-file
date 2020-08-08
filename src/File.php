<?php

namespace Kfriars\ArrayToFile;

use Kfriars\ArrayToFile\Contracts\FileContract;
use Kfriars\ArrayToFile\Exceptions\FileSaveException;

class File implements FileContract
{
    /**
     * Save the contents to a file
     *
     * @param string $filepath
     * @param string $content
     * @return void
     * @throws FileSaveException
     */
    public function save($filepath, $content)
    {
        if (file_put_contents($filepath, $content) === false) {
            throw new FileSaveException("Could not write the contents to {$filepath}");
        }
    }
}
