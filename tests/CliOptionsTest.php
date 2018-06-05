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

use PhpDocFormatter\CommandConfig;
use PhpDocFormatter\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class CliOptionsTest extends TestCase
{
    public function testEmptyCliOptions()
    {
        $argv = array(
            'vendor/bon/php-doc-formatter',
        );

        $options = CommandConfig::getCliOptions($argv);

        $this->assertNotTrue($options[CommandConfig::HELP]);
        $this->assertNotTrue($options[CommandConfig::VERSION]);
        $this->assertNull($options[CommandConfig::IDENT]);
        $this->assertNull($options[CommandConfig::NEWLINE]);
        $this->assertCount(0, $options[CommandConfig::EXCLUDE]);
        $this->assertCount(1, $options[CommandConfig::PATHS]);
    }

    public function testLongCliOptions()
    {
        $argv = array(
            'vendor/bon/php-doc-formatter',
            '--help',
            '--version',
            '--ident',
            '"\\t\\t"',
            '--newline',
            '"\\n\\n"',
            '--exclude',
            '"somepath/1',
            '--exclude',
            'somepath/2"',
            'path1',
            'path2',
        );

        $options = CommandConfig::getCliOptions($argv);

        $this->assertTrue($options[CommandConfig::HELP]);
        $this->assertTrue($options[CommandConfig::VERSION]);
        $this->assertEquals("\t\t", $options[CommandConfig::IDENT]);
        $this->assertEquals("\n\n", $options[CommandConfig::NEWLINE]);
        $this->assertEquals(array('somepath/1', 'somepath/2'), $options[CommandConfig::EXCLUDE]);
        $this->assertEquals(array('path1', 'path2'), $options[CommandConfig::PATHS]);
    }

    public function testShortCliOptions()
    {
        $argv = array(
            'vendor/bon/php-doc-formatter',
            '-h',
            '-v',
            '-i',
            '"\\t\\t"',
            '-n',
            '"\\n\\n"',
            '-e',
            '"somepath/1',
            '-e',
            'somepath/2"',
            'path1',
            'path2',
        );

        $options = CommandConfig::getCliOptions($argv);

        $this->assertTrue($options[CommandConfig::HELP]);
        $this->assertTrue($options[CommandConfig::VERSION]);
        $this->assertEquals("\t\t", $options[CommandConfig::IDENT]);
        $this->assertEquals("\n\n", $options[CommandConfig::NEWLINE]);
        $this->assertEquals(array('somepath/1', 'somepath/2'), $options[CommandConfig::EXCLUDE]);
        $this->assertEquals(array('path1', 'path2'), $options[CommandConfig::PATHS]);
    }

    public function testOptionsFile1()
    {
        $config = CommandConfig::getOptionsFile(__DIR__.'/Resources');
        $this->assertInstanceOf(Config::class, $config);

        $this->assertEquals("\t", $config->getIdent());
        $this->assertInstanceOf(Finder::class, $config->getFinder());
    }

    public function testOptionsFile2()
    {
        $config = CommandConfig::getOptionsFile(__DIR__.'/Resources2');
        $this->assertInstanceOf(Config::class, $config);

        $this->assertEquals('  ', $config->getIdent());
        $this->assertInternalType('array', $config->getFinder());
    }

    public function testMerge()
    {
        $config = Config::create();
        $config->setIdent("\t\t\t");
        $config->setNewLine("\n\n\n\n");
        $config->setFinder(array('p1'));

        $options = array(
            CommandConfig::IDENT => ' ',
            CommandConfig::NEWLINE => "\r",
            CommandConfig::PATHS => array('path1'),
        );

        CommandConfig::mergeOptions($config, $options);

        $this->assertEquals(' ', $config->getIdent());
        $this->assertEquals("\r", $config->getNewLine());
        $this->assertEquals(array('p1'), $config->getFinder());
    }

    public function testMergeFinder()
    {
        $config = Config::create();
        $config->setIdent("\t\t\t");
        $config->setNewLine("\n\n\n\n");

        $options = array(
            CommandConfig::IDENT => ' ',
            CommandConfig::NEWLINE => "\r",
            CommandConfig::EXCLUDE => array(),
            CommandConfig::PATHS => array(__DIR__.'/Resources2'),
        );

        CommandConfig::mergeOptions($config, $options);

        $this->assertEquals(' ', $config->getIdent());
        $this->assertEquals("\r", $config->getNewLine());
        $this->assertInstanceOf(Finder::class, $config->getFinder());
    }
}
