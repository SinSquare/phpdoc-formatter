<?php

/*
 * This file is part of the PHPDoc Formatter application.
 *
 * (c) Ábel Katona
 *
 * This source file is subject to the MIT license that is bundled with this source code in the file LICENSE.
 */

namespace PhpDocFormatter;

class Config
{
    private $finder;
    private $rules;
    private $ident;
    private $newLine;
    private $dryRun;

    private function __construct()
    {
    }

    public static function create()
    {
        $c = new self();
        $c->setIdent('    ');

        return $c;
    }

    /**
     * @return mixed
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * @param mixed $finder
     *
     * @return self
     */
    public function setFinder($finder)
    {
        if (!is_array($finder) && !$finder instanceof \Traversable) {
            throw new \InvalidArgumentException("'finder' must be an array or a Traversable class");
        }
        $this->finder = $finder;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param mixed $rules
     *
     * @return self
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param mixed $ident
     *
     * @return self
     */
    public function setIdent(string $ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewLine()
    {
        return $this->newLine;
    }

    /**
     * @param mixed $newLine
     *
     * @return self
     */
    public function setNewLine(string $newLine)
    {
        $this->newLine = $newLine;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDryRun()
    {
        return $this->dryRun;
    }

    /**
     * @param mixed $dryRun
     *
     * @return self
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;

        return $this;
    }
}
