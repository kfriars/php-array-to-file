<?php

namespace Kfriars\ArrayToFile;

use Kfriars\ArrayToFile\Contracts\FileContract;

class ArrayToFile
{
    /** @var FileContract Responsible for saving the file */
    protected $file;

    /** @var int The current indentation level for pretty printing */
    protected $indentationLevel = 0;

    /** @var int The number of spaces in a tab */
    protected $tabSize = 4;

    /** @var string The stringified version of the array */
    protected $content = '';


    public function __construct(
        FileContract $file
    ) {
        $this->file = $file;
    }

    /**
     * Create an instance of the array to file writer
     *
     * @return ArrayToFile
     */
    public static function make()
    {
        return new self(new File());
    }

    /**
     * Write the contents of an array to an includeable .php file
     *
     * @param array $array
     * @param string $filepath
     * @param mixed|null $transform
     * @return void
     * @throws FileSaveException
     */
    public function write($array, $filepath, $transform = null)
    {
        if ($transform === null) {
            $transform = function ($value) {
                if (is_string($value)) {
                    return preg_replace("/([^\\\])'/", "$1\'", $value);
                }

                return $value;
            };
        }

        $this->startArrayFile();
        $this->arrayToLines($array, $transform, $this->isAssoc($array));
        $this->finishArrayFile();

        $this->file->save($filepath, $this->content);
    }

    /**
     * Start the file contents and initialize indentation
     *
     * @return void
     */
    protected function startArrayFile()
    {
        $this->indentationLevel = 0;
        $this->content = '';
        $this->newLine('<?php');
        $this->newLine('');
        $this->newLine('return [');
        $this->indentationLevel = 1;
    }

    /**
     * Convert the array to a properly indented string
     *
     * @param array $array
     * @param callable $transform
     * @param bool $isAssoc
     * @return void
     */
    protected function arrayToLines($array, $transform, $isAssoc)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->newLine("'" . $key . "' => [");
                $this->indentationLevel++;
                $this->arrayToLines($value, $transform, $this->isAssoc($value));
                $this->indentationLevel--;
                $this->newLine('],');
            } elseif (is_object($value)) {
                $this->newLine("'" . $key . "' => [");
                $this->indentationLevel++;
                $cast = (array) $value;
                $this->arrayToLines($cast, $transform, $this->isAssoc($cast));
                $this->indentationLevel--;
                $this->newLine('],');
            } else {
                $key = $isAssoc ? "'" . $key . "' => " : "";
                $value = $transform($value);

                if (is_string($value)) {
                    $value = "'" . $value . "'";
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                $this->newLine($key . $value . ",");
            }
        }
    }

    /**
     * Finish the file contents and reset indentation
     *
     * @return void
     */
    protected function finishArrayFile()
    {
        $this->indentationLevel = 0;
        $this->newLine('];');
    }

    /**
     * Add a line to the file
     *
     * @param string $line
     * @return void
     */
    protected function newLine($line)
    {
        $spaces = str_repeat(' ', $this->indentationLevel * $this->tabSize);

        $this->content = $this->content . $spaces . $line . PHP_EOL;
    }

    /**
     * Check if a php array is associative, or sequential from 0
     *
     * @param $array
     * @return bool
     */
    protected function isAssoc($array)
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
