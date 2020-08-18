<?php

namespace Kfriars\ArrayToFile\Tests;

use Kfriars\ArrayToFile\ArrayToFile;
use Kfriars\ArrayToFile\Exceptions\FileSaveException;
use Kfriars\ArrayToFile\Facades\ArrayWriter;
use Kfriars\ArrayToFile\File;
use Mockery;
use PHPUnit\Framework\TestCase;

class ArrayToFileTest extends TestCase
{
    /** @var string */
    private $file = __DIR__.'/test.php';

    protected function tearDown(): void
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /** @test */
    public function handles_multiple_types()
    {
        $array = [
            'string' => 'String',
            'boolean' => false,
            'int' => 1,
            'array' => [
                'string' => 'Nested string',
                'boolean' => true,
                'int' => 0,
                'object' => (object) [
                    'string' => 'Object string',
                    'boolean' => false,
                    'int' => -3,
                ],
            ],
        ];

        ArrayWriter::toFile($array, $this->file);

        $included = include $this->file;
        $this->assertEquals('String', $included['string']);
    }

    /** @test */
    public function properly_indents_file()
    {
        $array = [
            'a' => [
                'b' => [
                    'a',
                    'b',
                    'c',
                ],
                'c' => [
                    'a',
                    'b',
                    'c',
                ],
            ],
        ];

        ArrayWriter::toFile($array, $this->file);

        $lines = file($this->file);
        $this->assertEquals("return [".PHP_EOL, $lines[2]);
        $this->assertEquals("    'a' => [".PHP_EOL, $lines[3]);
        $this->assertEquals("        'b' => [".PHP_EOL, $lines[4]);
        $this->assertEquals("            'a',".PHP_EOL, $lines[5]);
        $this->assertEquals("            'b',".PHP_EOL, $lines[6]);
        $this->assertEquals("            'c',".PHP_EOL, $lines[7]);
        $this->assertEquals("        ],".PHP_EOL, $lines[8]);
        $this->assertEquals("        'c' => [".PHP_EOL, $lines[9]);
        $this->assertEquals("            'a',".PHP_EOL, $lines[10]);
        $this->assertEquals("            'b',".PHP_EOL, $lines[11]);
        $this->assertEquals("            'c',".PHP_EOL, $lines[12]);
        $this->assertEquals("        ],".PHP_EOL, $lines[13]);
        $this->assertEquals("    ],".PHP_EOL, $lines[14]);
        $this->assertEquals("];".PHP_EOL, $lines[15]);
    }

    /** @test */
    public function omits_keys_from_sequential_arrays()
    {
        ArrayWriter::toFile(['a', 'b', 'c'], $this->file);

        $lines = file($this->file);
        $this->assertEquals("    'a',".PHP_EOL, $lines[3]);
        $this->assertEquals("    'b',".PHP_EOL, $lines[4]);
        $this->assertEquals("    'c',".PHP_EOL, $lines[5]);
    }

    /** @test */
    public function has_keys_for_associative_arrays()
    {
        ArrayWriter::toFile(['a' => 1, 'b' => 2, 'c' => 3, 'd' => true], $this->file);

        $lines = file($this->file);
        $this->assertEquals("    'a' => 1,".PHP_EOL, $lines[3]);
        $this->assertEquals("    'b' => 2,".PHP_EOL, $lines[4]);
        $this->assertEquals("    'c' => 3,".PHP_EOL, $lines[5]);
        $this->assertEquals("    'd' => true,".PHP_EOL, $lines[6]);
    }

    /** @test */
    public function can_transform_values_given_a_callable()
    {
        ArrayWriter::toFile([0, 1, '', ' '], $this->file, function ($value) {
            return (bool) $value;
        });

        $lines = file($this->file);
        $this->assertEquals("    false,".PHP_EOL, $lines[3]);
        $this->assertEquals("    true,".PHP_EOL, $lines[4]);
        $this->assertEquals("    false,".PHP_EOL, $lines[5]);
        $this->assertEquals("    true,".PHP_EOL, $lines[6]);
    }

    /** @test */
    public function throws_an_error_when_writing_fails()
    {
        $fileDouble = Mockery::mock(File::class)->makePartial();

        $fileDouble->shouldReceive('save')
                    ->andThrow(new FileSaveException("Could not write the contents to {$this->file}"));

        $this->expectException(FileSaveException::class);

        (new ArrayToFile($fileDouble))->write([1, 2, 3], $this->file);
    }

    /** @test */
    public function creates_a_new_directory_if_one_does_not_exist()
    {
        $inFolder = __DIR__.'/folder/test.php';

        ArrayWriter::toFile(['a' => 1, 'b' => 2, 'c' => 3, 'd' => true], $inFolder);

        $this->assertTrue(file_exists($inFolder));
    }
}
