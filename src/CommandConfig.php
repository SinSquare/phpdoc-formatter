<?php

/*
 * This file is part of the PHPDoc Formatter application.
 * https://github.com/SinSquare/phpdoc-formatter
 *
 * (c) Ábel Katona
 *
 * This source file is subject to the MIT license that is bundled with this source code in the file LICENSE.
 */

namespace PhpDocFormatter;

use Symfony\Component\Finder\Finder;

/**
 * @author Abel Katona
 */
class CommandConfig
{
    const EXCLUDE = 'exclude';
    const HELP = 'help';
    const VERSION = 'version';
    const IDENT = 'ident';
    const NEWLINE = 'newline';
    const PATHS = 'paths';

    const OPTIONS = [
        self::PATHS => [],
        self::EXCLUDE => [],
        self::HELP => false,
        self::VERSION => false,
        self::IDENT => null,
        self::NEWLINE => null,
    ];
    const ALIASES = [
        'e' => self::EXCLUDE,
        'v' => self::HELP,
        'h' => self::VERSION,
        'i' => self::IDENT,
        'n' => self::NEWLINE,
    ];
    const NEEDSARGUMENT = [
        self::EXCLUDE,
        self::IDENT,
        self::NEWLINE,
    ];

    private $argc;

    public static function getCliOptions(array $argv)
    {
        $options = self::OPTIONS;
        $options[self::PATHS] = array();

        for ($i = 1; $i < count($argv); ++$i) {
            $arg = $argv[$i];
            if ('--' === substr($arg, 0, 2)) { // longopt
                $option = substr($arg, 2);
            } elseif ('-' === $arg[0]) { // shortopt
                if (array_key_exists(substr($arg, 1), self::ALIASES)) {
                    $option = self::ALIASES[$arg[1]];
                } else {
                    throw new Exception('Unknown option alias: "'.$arg.'"');
                }
            } else {
                $options[self::PATHS][] = $arg;
                continue;
            }

            if (false === array_key_exists($option, $options)) {
                throw new \Exception('Unknown option: "'.$arg.'"');
            }
            if (in_array($option, self::NEEDSARGUMENT)) {
                if (empty($argv[$i + 1]) || '-' === $argv[$i + 1][0]) {
                    throw new \Exception('Missing argument for "'.$arg.'"');
                }
                if (is_array($options[$option])) {
                    $options[$option][] = $argv[$i + 1];
                } else {
                    $options[$option] = $argv[$i + 1];
                }
                ++$i;
            } else {
                $options[$option] = true;
            }
        }

        if (null !== $options[self::IDENT]) {
            $i = $options[self::IDENT];
            $i = trim($i, "\"'");
            $i = str_replace('\\t', "\t", $i);
            $options[self::IDENT] = $i;
        }

        if (null !== $options[self::NEWLINE]) {
            $nl = $options[self::NEWLINE];
            $nl = trim($nl, "\"'");
            $nl = str_replace('\\n', "\n", $nl);
            $nl = str_replace('\\r', "\r", $nl);
            $options[self::NEWLINE] = $nl;
        }

        $exclude = array();
        if ($options[self::EXCLUDE]) {
            $exclude = $options[self::EXCLUDE];
            $exclude = array_map(function ($item) {return trim($item, "\"'"); }, $exclude);
        }
        $options[self::EXCLUDE] = $exclude;

        if (0 === count($options[self::PATHS])) {
            $options[self::PATHS][] = getcwd();
        }

        return $options;
    }

    public static function getOptionsFile(string $path)
    {
        $path = rtrim($path, '/\\');
        $candidates = [
            $path.DIRECTORY_SEPARATOR.'.phpdoc',
            $path.DIRECTORY_SEPARATOR.'.phpdoc.dist',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path) && is_readable($path)) {
                $config = self::separatedContextLessInclude($path);
                if ($config instanceof Config) {
                    return $config;
                }
            }
        }

        return Config::create();
    }

    public static function mergeOptions(Config $config, array $options)
    {
        if (null === $config->getFinder()) {
            $finder = new Finder();
            foreach ($options[self::PATHS] as $p) {
                $finder->in($p);
            }
            if (is_array($options[self::EXCLUDE]) && count($options[self::EXCLUDE]) > 0) {
                foreach ($options[self::EXCLUDE] as $p) {
                    $finder->exclude($p);
                }
            }

            $config->setFinder($finder);
        }

        if (null !== $options[self::IDENT]) {
            $i = $options[self::IDENT];
            $i = str_replace('\\t', "\t", $i);
            $config->setIdent($i);
        }

        if (null !== $options[self::NEWLINE]) {
            $nl = $options[self::NEWLINE];
            $nl = str_replace('\\n', "\n", $nl);
            $nl = str_replace('\\r', "\r", $nl);
            $config->setNewLine($nl);
        }
    }

    private static function separatedContextLessInclude($path)
    {
        return include $path;
    }
}
