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
        $this->assertEquals("return [\n", $lines[2]);
        $this->assertEquals("    'a' => [\n", $lines[3]);
        $this->assertEquals("        'b' => [\n", $lines[4]);
        $this->assertEquals("            'a',\n", $lines[5]);
        $this->assertEquals("            'b',\n", $lines[6]);
        $this->assertEquals("            'c',\n", $lines[7]);
        $this->assertEquals("        ],\n", $lines[8]);
        $this->assertEquals("        'c' => [\n", $lines[9]);
        $this->assertEquals("            'a',\n", $lines[10]);
        $this->assertEquals("            'b',\n", $lines[11]);
        $this->assertEquals("            'c',\n", $lines[12]);
        $this->assertEquals("        ],\n", $lines[13]);
        $this->assertEquals("    ],\n", $lines[14]);
        $this->assertEquals("];\n", $lines[15]);
    }

    /** @test */
    public function omits_keys_from_sequential_arrays()
    {
        ArrayWriter::toFile(['a', 'b', 'c'], $this->file);

        $lines = file($this->file);
        $this->assertEquals("    'a',\n", $lines[3]);
        $this->assertEquals("    'b',\n", $lines[4]);
        $this->assertEquals("    'c',\n", $lines[5]);
    }

    /** @test */
    public function has_keys_for_associative_arrays()
    {
        ArrayWriter::toFile(['a' => 1, 'b' => 2, 'c' => 3, 'd' => true], $this->file);

        $lines = file($this->file);
        $this->assertEquals("    'a' => 1,\n", $lines[3]);
        $this->assertEquals("    'b' => 2,\n", $lines[4]);
        $this->assertEquals("    'c' => 3,\n", $lines[5]);
        $this->assertEquals("    'd' => true,\n", $lines[6]);
    }

    /** @test */
    public function can_transform_values_given_a_callable()
    {
        ArrayWriter::toFile([0, 1, '', ' '], $this->file, function ($value) {
            return (bool) $value;
        });

        $lines = file($this->file);
        $this->assertEquals("    false,\n", $lines[3]);
        $this->assertEquals("    true,\n", $lines[4]);
        $this->assertEquals("    false,\n", $lines[5]);
        $this->assertEquals("    true,\n", $lines[6]);
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
}
