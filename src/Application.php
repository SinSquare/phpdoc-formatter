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
                    $offset = $value['offset'] + $value['length'];
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
        static $eols = array(
            "\0x000D000A", // [UNICODE] CR+LF: CR (U+000D) followed by LF (U+000A)
            "\0x000A",     // [UNICODE] LF: Line Feed, U+000A
            "\0x000B",     // [UNICODE] VT: Vertical Tab, U+000B
            "\0x000C",     // [UNICODE] FF: Form Feed, U+000C
            "\0x000D",     // [UNICODE] CR: Carriage Return, U+000D
            "\0x0085",     // [UNICODE] NEL: Next Line, U+0085
            "\0x2028",     // [UNICODE] LS: Line Separator, U+2028
            "\0x2029",     // [UNICODE] PS: Paragraph Separator, U+2029
            "\0x0D0A",     // [ASCII] CR+LF: Windows, TOPS-10, RT-11, CP/M, MP/M, DOS, Atari TOS, OS/2, Symbian OS, Palm OS
            "\0x0A0D",     // [ASCII] LF+CR: BBC Acorn, RISC OS spooled text output.
            "\0x0A",       // [ASCII] LF: Multics, Unix, Unix-like, BeOS, Amiga, RISC OS
            "\0x0D",       // [ASCII] CR: Commodore 8-bit, BBC Acorn, TRS-80, Apple II, Mac OS <=v9, OS-9
            "\0x1E",       // [ASCII] RS: QNX (pre-POSIX)
            //"\0x76",       // [?????] NEWLINE: ZX80, ZX81 [DEPRECATED]
            "\0x15",       // [EBCDEIC] NEL: OS/390, OS/400
        );
        $cur_cnt = 0;
        $cur_eol = "\n";
        foreach($eols as $eol){
            if(($count = substr_count($file, $eol)) > $cur_cnt){
                $cur_cnt = $count;
                $cur_eol = $eol;
            }
        }
        return $cur_eol;
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
                $i = $ident + $c > 0 ? $ident + $c : 0;
                $line = str_repeat($this->config->getIdent(), $i).$line;
            }

            $ident += $c;
            if($ident < 0) {
                //TODO warning - possible open-close tag mismatch
                $ident = 0;
            }
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
            $line = rtrim($line);
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
