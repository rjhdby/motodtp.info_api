<?php


namespace converters;


use db\ApkDb;

abstract class AbstractConverter implements Converter
{
    private $sql;

    /**
     * AbstractConverter constructor.
     * @param $sql
     */
    public function __construct($sql) {
        $this->sql = $sql;
    }

    protected function getContent() {
        return ApkDb::getInstance()->query($this->sql);
    }
}