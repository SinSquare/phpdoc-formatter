<?php

/*
 * This file is part of the PHPDoc Formatter application.
 *
 * (c) Ábel Katona
 *
 * This source file is subject to the MIT license that is bundled with this source code in the file LICENSE.
 */

namespace PhpDocFormatter;

use Symfony\Component\Stopwatch\Stopwatch;

class Application
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function fixFiles()
    {
        $stopwatch = new Stopwatch();
        $finder = $this->config->getFinder();
        foreach ($finder as $file) {
            $content = file_get_contents($file);
            if (false === $content) {
                //TODO warning
            }

            if (null === $this->config->getNewLine()) {
                $this->config->setNewLine($this->getFileDominantLineEnding($content));
            }

            $docComments = $this->findAllDocDomment($content);

            if (count($docComments)) {
                foreach ($docComments as $key => $match) {
                    $value = $match['match'];
                    $ident = $this->getDocBodyIdent($value);
                    if (null === $ident) {
                        //TODO warning
                        continue;
                    }

                    $norm = $this->getDocBody($value);
                    $norm = $this->normalizeDocBody($norm);
                    $norm = $this->formatDocBody($norm, $ident);
                    $docComments[$key]['formatted'] = $norm;
                }

                $newFile = '';
                $offset = 0;
                foreach ($docComments as $key => $value) {
                    $newFile .= substr($content, $offset, $value['offset'] - $offset);
                    $offset = $value['offset'] + $value['length'] + 1;
                    $newFile .= $value['formatted'];
                }
                $newFile .= substr($content, $offset);

                if (sha1($content) !== sha1($newFile)) {
                    $d = file_put_contents($file, $newFile);
                    if (false === $d) {
                        //TODO warning
                    }
                    echo sprintf("Fixed %s\n", (string) $file);
                }
            }
        }
    }

    private function getFileDominantLineEnding(string $file)
    {
        $n = preg_match_all('/(?<!\r)\n/', $file);
        $rn = preg_match_all('/(?<=\r)\n/i', $file);

        if ($rn > $n) {
            return "\r\n";
        }

        return "\n";
    }

    private function getDocBodyIdent(string $doc)
    {
        $lines = explode("\n", $doc);

        foreach ($lines as $line) {
            if (preg_match("#/\*\*#", $line, $matches, PREG_OFFSET_CAPTURE)) {
                return $matches[0][1];
            }
        }

        return null;
    }

    private function getDocBody(string $doc)
    {
        $lines = explode("\n", $doc);

        foreach ($lines as $key => &$line) {
            $line = trim($line);
            if ('*/' == $line || '/**' == $line) {
                unset($lines[$key]);
            }
            $line = ltrim($line, '*');
            $line = trim($line);
        }

        $lines = implode("\n", $lines);
        $lines = trim($lines);

        $lines = preg_replace("#(?<!\n)[\n]{3,}#", "\n\n\n", $lines, -1, $count);

        return $lines;
    }

    private function normalizeDocBody(string $doc)
    {
        $lines = explode("\n", $doc);
        $ident = 0;

        foreach ($lines as $key => &$line) {
            $c = 0;

            $c += substr_count($line, '(');
            $c -= substr_count($line, ')');
            $c += substr_count($line, '{');
            $c -= substr_count($line, '}');
            $c += substr_count($line, '[');
            $c -= substr_count($line, ']');

            if ($c > 0) {
                $line = str_repeat($this->config->getIdent(), $ident).$line;
            } else {
                $line = str_repeat($this->config->getIdent(), $ident + $c).$line;
            }

            $ident += $c;
        }

        return implode("\n", $lines);
    }

    private function formatDocBody(string $doc, int $ident)
    {
        $newLine = $this->config->getNewLine();
        $lines = explode("\n", $doc);

        foreach ($lines as $key => &$line) {
            $line = trim($line, "\r");
            $line = str_repeat(' ', $ident).' * '.$line;
        }

        $doc = implode($newLine, $lines);
        $doc = str_repeat(' ', $ident).'/**'.$newLine.$doc;
        $doc .= $newLine.str_repeat(' ', $ident).' */'.$newLine;

        return $doc;
    }

    private function findAllDocDomment(string $file)
    {
        $regex = "#[\h]*/\*\*[\s]?([\s]*\*[^\n]*[\s]?)+[\h]*\*/[\s]?#";

        $offset = 0;

        $matches = array();

        while (preg_match($regex, $file, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $matches[] = array(
                'offset' => $m[0][1],
                'length' => strlen($m[0][0]),
                'match' => $m[0][0],
            );
            $offset = $m[0][1] + strlen($m[0][0]);
        }

        return $matches;
    }
}
