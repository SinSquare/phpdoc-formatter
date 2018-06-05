<?php

/*
 * This file is part of the PHPDoc Formatter application.
 * https://github.com/SinSquare/phpdoc-formatter
 *
 * (c) Ábel Katona
 *
 * This source file is subject to the MIT license that is bundled with this source code in the file LICENSE.
 */

namespace PhpDocFormatter\Tests;

use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    private $path;

    protected function setUp()
    {
        $path = sys_get_temp_dir().'/PHPDOCTEST';
        if (file_exists($path)) {
            $this->rrmdir($path);
        }
        if (!mkdir($path, 0777, true)) {
            throw new \Exception('Could not make temp folder');
        }

        $this->copydir(__DIR__.'/Resources', $path.'/Resources');
        $this->copydir(__DIR__.'/Resources2', $path.'/Resources2');

        $this->path = $path;
    }

    private function copydir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (('.' != $file) && ('..' != $file)) {
                if (is_dir($src.'/'.$file)) {
                    $this->copydir($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }

    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ('.' != $object && '..' != $object) {
                    if ('dir' == filetype($dir.'/'.$object)) {
                        $this->rrmdir($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function testMergeFinder()
    {
        exec(__DIR__.'/../php-doc-formatter '.escapeshellarg($this->path), $output, $retval);
        $this->assertSame(0, $retval);

        $this->assertEquals('f290d79948f4337caf29371bbf88af0b93faeaa0', sha1_file($this->path.'/Resources/AbstractApiController.php'));
        $this->assertEquals('a453e1ba0639b8d723b7974ab12b326e00022f0f', sha1_file($this->path.'/Resources/CurrencyController.php'));
        $this->assertEquals('056405be2fc59df6ce27dc3ba5293cec1fcc95e3', sha1_file($this->path.'/Resources2/AbstractApiController.php'));
    }
}
