<?php

namespace Test\Hellpers;

use PHPUnit\Framework\TestCase;
use Hellpers\Structurer;
use DateTime;

class StructurersTest extends TestCase
{
    private $root       = '';
    private $structurer = null;

    protected function setUp()
    {
        $this->root = __DIR__ . '/test';

        if (file_exists($this->root)) {
            $this->removeRoot($this->root);
        }

        mkdir($this->root);

        $this->structurer = new Structurer($this->root);
    }

    protected function tearDown()
    {
        $this->removeRoot($this->root);
    }

    private function removeRoot(string $path): bool
    {
        if (is_file($path)) {
            return unlink($path);
        }

        if (is_dir($path)) {
            foreach (scandir($path) as $p) {
                if ($p === '.' or $p === '..') {
                    continue;
                }

                $this->removeRoot($path . DIRECTORY_SEPARATOR . $p);
            }

            return rmdir($path); 
        }

        return false;
    }

    public function testFolder()
    {
        $this->assertFileNotExists("{$this->root}/path");
        $this->structurer = $this->structurer->folder('path');
        $this->assertFileExists("{$this->root}/path");

        $this->assertFileNotExists("{$this->root}/path/to");
        $this->structurer = $this->structurer->folder('to');
        $this->assertFileExists("{$this->root}/path/to");

        $this->assertFileNotExists("{$this->root}/path/to/folder");
        $this->structurer = $this->structurer->folder('folder');
        $this->assertFileExists("{$this->root}/path/to/folder");
    }

    public function testBack()
    {
        $this->assertFileNotExists("{$this->root}/path1");
        $this->structurer = $this->structurer->folder('path1');
        $this->assertFileExists("{$this->root}/path1");

        $this->assertFileNotExists("{$this->root}/path2");
        $this->structurer = $this->structurer->back()->folder('path2');
        $this->assertFileExists("{$this->root}/path2");
    }

    public function testFile()
    {
        $this->assertFileNotExists("{$this->root}/path/to/file1.txt");
        $this->structurer = $this->structurer->folder('path')->folder('to')
            ->file('file1.txt');
        $this->assertFileExists("{$this->root}/path/to/file1.txt");

        $this->assertFileNotExists("{$this->root}/path/file2.txt");
        $this->structurer = $this->structurer->back()->file('file2.txt');
        $this->assertFileExists("{$this->root}/path/file2.txt");
    }

    public function testContent()
    {
        $content = 'Hello, World!';
        $add     = ' Add String...';

        $this->structurer = $this->structurer->file('file.txt')
            ->content($content);

        $this->assertStringEqualsFile("{$this->root}/file.txt", $content);

        $this->structurer = $this->structurer->content($add, true);

        $this->assertStringEqualsFile(
            "{$this->root}/file.txt", $content . $add
        );
    }

    public function testMode()
    {
        if (mb_stristr(mb_strtolower(php_uname('s')), 'win') === false) {
            $this->structurer->file('file.txt')->mode(['chmod' => 0664]);

            $rights = fileperms("{$this->root}/file.txt");
            $rights = substr(sprintf('%o', $rights), -4);
            $rights = (int)$rights;

            $this->assertEquals($rights, 664);
        }
    }

    public function testPath()
    {
        $this->structurer = $this->structurer->folder('folder')
            ->file('file.txt');

        $this->assertEquals($this->structurer->path(), "{$this->root}/folder");

        $this->assertEquals(
            $this->structurer->path(true), "{$this->root}/folder/file.txt"
        );
    }

    public function testLs()
    {
        $test = ['1.txt', '2.txt'];

        $ls = $this->structurer->file('1.txt')->file('2.txt')->ls();

        $this->assertEquals($test, $ls);
    }

    public function testD()
    {
        $year = (new DateTime())->format('Y');

        $this->assertFileNotExists("{$this->root}/$year");
        $this->structurer->folder(Structurer::d('Y'))->path();
        $this->assertFileExists("{$this->root}/$year");
    }

    public function testMake()
    {
        $this->assertFileNotExists("{$this->root}/folder");
        Structurer::make("{$this->root}/folder");
        $this->assertFileExists("{$this->root}/folder");
        $this->assertTrue(is_dir("{$this->root}/folder"));

        $this->assertFileNotExists("{$this->root}/folder2/file.txt");
        Structurer::make("{$this->root}/folder2", 'file.txt');
        $this->assertFileExists("{$this->root}/folder2/file.txt");
        $this->assertTrue(is_file("{$this->root}/folder2/file.txt"));
    }

    public function testCmake()
    {
        $this->assertFileNotExists("{$this->root}/file.txt");

        Structurer::cmake("{$this->root}/file.txt");

        $this->assertFileExists("{$this->root}/file.txt");

        $this->assertTrue(is_file("{$this->root}/file.txt"));

        $this->assertStringEqualsFile("{$this->root}/file.txt", '');

        Structurer::cmake("{$this->root}/file.txt", 'abc');

        $this->assertStringEqualsFile("{$this->root}/file.txt", 'abc');

        Structurer::cmake("{$this->root}/file.txt", 'xyz', true);

        $this->assertStringEqualsFile("{$this->root}/file.txt", 'abcxyz');

        Structurer::cmake("{$this->root}/file.txt", 'qwerty');

        $this->assertStringEqualsFile("{$this->root}/file.txt", 'qwerty');

        Structurer::cmake("{$this->root}/file.txt");

        $this->assertStringEqualsFile("{$this->root}/file.txt", 'qwerty');
    }
}
