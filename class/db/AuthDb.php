<?php

namespace db;

use core\Config;
use PDO;
use PDOException;

class AuthDb
{
    const HOST = 'auth.host';
    const USER = 'auth.login';
    const PASS = 'auth.password';
    const DB   = 'auth.db';

    /** @var PDO $db */
    private static $db;

    private function __construct () { }

    /**
     * @return PDO
     */
    public static function getInstance () {
        if (null === self::$db) {
            self::connect();
        }
        return self::$db;
    }

    private static function connect () {
        try {
            self::$db = new PDO(self::getConnectionString(), Config::get(self::USER), Config::get(self::PASS), [PDO::ATTR_PERSISTENT => true]);
            self::setAttributes();
        } catch (PDOException $e) {
            echo $e->getTraceAsString();
            throw $e;
        }
    }

    /**
     * @return string
     */
    private static function getConnectionString () {
        return 'mysql:' .
               'host=' . Config::get(self::HOST) . ';' .
               'dbname=' . Config::get(self::DB) . ';' .
               'charset=utf8';
    }

    private static function setAttributes () {
        self::$db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        self::$db->query("SET NAMES UTF8");
    }
}