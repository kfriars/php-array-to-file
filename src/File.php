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
        
        if (! $this->directoryExists($directory)) {
            if ($this->makeDirectory($directory)) {
                throw new FileSaveException("Could not create the directory '{$directory}' to save the file.");
            }
        }

        if ($this->writeToFile($filepath, $content)) {
            throw new FileSaveException("Could not write the contents to {$filepath}");
        }
    }

    /**
     * Get the directory from a given file path
     *
     * @param string $filepath
     * @return string
     */
    protected function directory($filepath)
    {
        $directory = explode(DIRECTORY_SEPARATOR, $filepath);
        array_pop($directory);

        return implode(DIRECTORY_SEPARATOR, $directory);
    }

    /**
     * Determine if the directory exists
     *
     * @param string $directory
     * @return bool
     */
    protected function directoryExists(string $directory)
    {
        return file_exists($directory);
    }

    /**
     * Make a directory
     *
     * @param string $directory
     * @return bool
     */
    protected function makeDirectory(string $directory): bool
    {
        return mkdir($directory, 0775, true) === false;
    }

    /**
     * Write the contents to a file
     *
     * @param string $filepath
     * @param string $content
     * @return bool
     */
    protected function writeToFile(string $filepath, string $content): bool
    {
        return file_put_contents($filepath, $content) === false;
    }
}
