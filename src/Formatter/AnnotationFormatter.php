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
class AnnotationFormatter
{
    public static function format(string $data)
    {
        $inline = strpos($data, "\n") === false ? true : false;




        
    }

    public static function extractAllAnnotation(string $data)
    {
        $offset = 0;
        $annotatins = array();

        preg_match("#(?<!\{)@[a-z]+[a-z0-9\\\_\-/]*\(#i", $value, $matches, PREG_OFFSET_CAPTURE, $offset);










    }




}