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

/**
 * @author Abel Katona
 */
class Config
{
    private $finder;
    private $rules;
    private $ident;
    private $newLine;

    private function __construct()
    {
        $this->rules = array();
        $this->ident = '    ';
    }

    /**
     * @return Config Default config
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Validates the configuration.
     *
     * @throws \Exception if the config is not valid
     */
    public function validate()
    {
        if (preg_match('/^[^\h]*$/', $this->ident)) {
            throw new \Exception('\'ident\' must contain whitespace only!');
        }

        if (null !== $this->newLine) {
            if (preg_match('/^[^\n\r]*$/', $this->newLine)) {
                throw new \Exception('\'newLine\' must contain new line and carriage return only!');
            }
        }

        if (!is_array($this->finder) && !$this->finder instanceof \Traversable) {
            throw new \Exception("'finder' must be an array or a Traversable object!");
        }
    }

    /**
     * @return Finder|Traversable
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * @param Finder|Traversable $finder
     *
     * @return self
     */
    public function setFinder($finder)
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param array $rules
     *
     * @return self
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param string $ident
     *
     * @return self
     */
    public function setIdent(string $ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewLine()
    {
        return $this->newLine;
    }

    /**
     * @param string $newLine
     *
     * @return self
     */
    public function setNewLine(string $newLine)
    {
        $this->newLine = $newLine;

        return $this;
    }
}
