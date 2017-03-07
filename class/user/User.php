<?php

namespace user;

use db\ApkDb;
use db\AuthDb;
use errors\Codes;

class User
{
    const READ_ONLY = 0;
    const STANDARD  = 1;
    const MODERATOR = 2;
    const DEVELOPER = 3;

    public static $id;
    public static $login = '';
    public static $role  = self::STANDARD;

    private static $masterPassHashSQL  = 'SELECT members_pass_hash, members_pass_salt FROM members WHERE name=:login';
    private static $roleSQL            = 'SELECT id, role FROM users WHERE login=:login';
    private static $createUserSQL      = 'INSERT INTO users (login, register) VALUES (:login, NOW())';
    private static $updateLastGetSQL   = 'UPDATE users SET lastgetlist = NOW() WHERE id=:id';
    private static $updateLastLoginSQL = 'UPDATE users SET lastlogin = NOW() WHERE id=:id';

    public static function getLastUpdateTime($id)
    {
        if ($id === 0) return false;
        $stmt = ApkDb::getInstance()->prepare('SELECT IFNULL(lastgetlist,DATE_SUB(NOW(), INTERVAL 24 HOUR)) FROM users WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_NUM);
        return $result ? $result[0] : false;
    }

    public static function setLastUpdateTime($id)
    {
        if ($id === 0) return;
        $stmt = ApkDb::getInstance()->prepare(self::$updateLastGetSQL);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function auth($login, $passHash)
    {
        $stmt = AuthDb::getInstance()->prepare(self::$masterPassHashSQL);
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new \InvalidArgumentException("No such user", Codes::NO_USER);
        }
        $masterPassHash = $result['members_pass_hash'];
        $masterSalt     = $result['members_pass_salt'];
        if (md5(md5($masterSalt) . $passHash) !== $masterPassHash) {
            throw new \InvalidArgumentException("Wrong username or password", Codes::WRONG_CREDENTIALS);
        }
        $result = self::fetchInfo($login);
        if ($result === false) {
            $result = self::createUser($login);
        }
        self::$login = $login;
        self::$id    = $result['id'];
        self::$role  = self::parseRole($result['role']);
        $stmt        = ApkDb::getInstance()->prepare(self::$updateLastLoginSQL);
        $stmt->bindValue(':id', self::$id);
        $stmt->execute();
    }

    public static function fetchInfo($login)
    {
        $stmt = ApkDb::getInstance()->prepare(self::$roleSQL);
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    private static function createUser($login)
    {
        $stmt = ApkDb::getInstance()->prepare(self::$createUserSQL);
        $stmt->bindValue(':login', $login);
        $stmt->execute();

        return ['id' => ApkDb::getInstance()->lastInsertId(), 'role' => self::STANDARD];
    }

    private static function parseRole($role)
    {
        switch ($role) {
            case 'readonly':
                return self::READ_ONLY;
            case 'moderator':
                return self::MODERATOR;
            case 'developer':
                return self::DEVELOPER;
            default:
                return self::STANDARD;
        }
    }
}