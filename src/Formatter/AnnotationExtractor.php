<?php

/*
 * This file is part of the PHPDoc Formatter application.
 * https://github.com/SinSquare/phpdoc-formatter
 *
 * (c) Ábel Katona
 *
 * This source file is subject to the MIT license that is bundled with this source code in the file LICENSE.
 */

namespace PhpDocFormatter\Formatter;

/**
 * @author Abel Katona
 */
class AnnotationExtractor
{
    const PARAMETER = "parameter";
    const ARGUMENT = "argument";
    const ANNOTATION = "annotation";




    public static function format(string $data)
    {
        $inline = strpos($data, "\n") === false ? true : false;

        $lines = explode("\n", $doc);

        foreach ($lines as $key => $line) {
            $parts = array();
            //extract annotations
            self::extractAnnotations($line, $parts);
            //extract parameters
            self::extractWithPattern($line, $parts, "#(?<=\(|,)[\h]*[a-z]+[a-z0-9]*[\h]*\=[\h]*\"[^\"]+\"[\h](?=\(|,)*#i", self::PARAMETER);
            //extract arguments
            self::extractWithPattern($line, $parts, "#(?<=\(|,)[\h]*[a-z]+[a-z0-9]*[\h]*\=[\h]*\"[^\"]+\"[\h](?=\(|,)*#i", self::ARGUMENT);
            self::extractWithPattern($line, $parts, "#(?<=\(|,)[\h]*[0-9a-z]+[\h]*(?=\(|,)#i", self::ARGUMENT);
        }


        
    }

    private static function extractWithPattern(string $data, &array $parts, string $pattern, $type)
    {
        if(!is_array($parts)) {
            $parts = array();
        }

        while(preg_match($pattern, $data, $matches, PREG_OFFSET_CAPTURE)) {
            $row = array(
                "type" => $type,
                "value" => $matches[0][0]
            );
            $key = "<#".count($parts).">";
            $data = substr($data, 0, $matches[0][1]).$key.substr($data, $matches[0][1] + strlen($matches[0][0]));
            $parts[$key] = $row;
        }

        return $data;
    }

    private static function extractAnnotations(string $data, &array $parts)
    {
        if(!is_array($parts)) {
            $parts = array();
        }

        //remove values between "-s because it whould cause problems...
        $nData = $data;
        $offset = 0;
        while(preg_match("#\"[^\"]+\"#i", $nData, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $replacement = "\"".str_repeat("a", strlen($matches[0][0])-2)."\"";
            $nData = substr($data, 0, $matches[0][1]).$replacement.substr($data, $matches[0][1] + strlen($matches[0][0]));
            $offset = $matches[0][1] + 1;
        }

        $offset = 0;

        while(preg_match("#@[a-z]+[a-z0-9\\\_\-/]*\(#i", $nData, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $o = $matches[0][1] + strlen($matches[0][0]);
            $value = null;
            do {
                $p = strpos($nData, ")", $o);
                if($p === false) {
                    //possible bracket mismatch
                    $offset = $o;
                    $value = null;
                    break;
                }
                $o = $p + 1;
                $value = substr($nData, $matches[0][1], $p - $matches[0][1] + 1);
                $oc = substr_count($value, "(");
                $cc = substr_count($value, ")");
            } while($oc !== $cc);

            if($value === null) {
                break;
            }

            $row = array(
                "type" => self::ANNOTATION,
                "value" => $value
            );
            $key = "<#".count($parts).">";
            $data = substr($data, 0, $matches[0][1]).$key.substr($data, $matches[0][1] + strlen($value));
            $nData = substr($nData, 0, $matches[0][1]).$key.substr($nData, $matches[0][1] + strlen($value));
            $parts[$key] = $row;
        }

        return $data;
    }
}