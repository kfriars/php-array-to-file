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
        $directory = $this->directory($filepath);
        
        if (! file_exists($directory)) {
            if (mkdir($directory, 0775, true) === false) {
                throw new FileSaveException("Could not create the directory '{$directory}' to save the file.");
            }
        }

        if (file_put_contents($filepath, $content) === false) {
            throw new FileSaveException("Could not write the contents to {$filepath}");
        }
    }

    /**
     * Get the directory from a given file path
     *
     * @param string $filepath
     * @return string
     */
    private function directory($filepath)
    {
        $directory = explode(DIRECTORY_SEPARATOR, $filepath);
        array_pop($directory);

        return implode(DIRECTORY_SEPARATOR, $directory);
    }
}
