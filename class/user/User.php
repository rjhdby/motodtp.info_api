<?php

namespace user;

use core\Config;
use db\ApkDb;
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

    private static $masterPassHashSQL  = 'SELECT `hash`, salt FROM users WHERE login=:login AND vkId IS NULL';
    private static $roleSQL            = 'SELECT id, role FROM users WHERE login=:login';
    private static $createUserSQL      = 'INSERT INTO users (login, register) VALUES (:login, NOW())';
    private static $createVkUserSQL    = 'INSERT INTO users (login, register, vkId) VALUES (:login, NOW(),:vkId)';
    private static $getVkUserSQL       = 'SELECT id, role, login FROM users WHERE vkId=:vkId';
    private static $updateLastGetSQL   = 'UPDATE users SET lastgetlist = NOW() WHERE id=:id';
    private static $updateLastLoginSQL = 'UPDATE users SET lastlogin = NOW() WHERE id=:id';
    private static $createGooglrUser   = 'INSERT INTO users (login, register, email) VALUES (:login, NOW(), :email)';
    private static $getGoogleUser      = 'SELECT id, role, login FROM users WHERE login=:login, email=:email';

    public static function getLastUpdateTime($id) {
        if ($id === 0) return false;
        $stmt = ApkDb::getInstance()->prepare('SELECT IFNULL(lastgetlist,DATE_SUB(NOW(), INTERVAL 24 HOUR)) FROM users WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_NUM);
        return $result ? $result[0] : false;
    }

    public static function setLastUpdateTime($id) {
        if ($id === 0) return;
        $stmt = ApkDb::getInstance()->prepare(self::$updateLastGetSQL);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function auth(...$args) {
        if (count(func_get_args()) == 2) {
            self::authForum($args[0], $args[1]);
        } else {
            self::authVk($args[0]);
        }
    }

    private static function authForum($login, $passHash) {
        $stmt = ApkDb::getInstance()->prepare(self::$masterPassHashSQL);
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new \InvalidArgumentException("No such user", Codes::NO_USER);
        }
        $masterPassHash = $result['hash'];
        $masterSalt     = $result['salt'];
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
        self::updateLastLogin();
    }

    public static function authGoogle($email, $name) {
        $stmt = ApkDb::getInstance()->prepare(self::$getGoogleUser);
        $stmt->bindValue(':login', $name);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            self::createGoogleUser($email, $name);
        } else {
            self::$role  = self::parseRole($result['role']);
            self::$login = $result['login'];
            self::$id    = $result['id'];
        }
        self::updateLastLogin();
    }

    private static function authVk($key) {
        $info = json_decode(file_get_contents(Config::get('vkApiUrl') . '=' . $key), true)['response'][0];
        if (!isset($info['id'])) throw new \InvalidArgumentException("No such user", Codes::NO_USER);
        self::$login = $info['first_name'] . ' ' . $info['last_name'];
        $stmt = ApkDb::getInstance()->prepare(self::$getVkUserSQL);
        $stmt->bindValue(':vkId', $info['id']);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            self::createVkUser($info);
        } else {
            self::$role  = self::parseRole($result['role']);
            self::$login = $result['login'];
            self::$id    = $result['id'];
        }
        self::updateLastLogin();
    }

    private static function createVkUser($info) {
        $stmt        = ApkDb::getInstance()->prepare(self::$createVkUserSQL);
        $stmt->bindValue(':login', self::$login);
        $stmt->bindValue(':vkId', $info['uid']);
        $stmt->execute();
        self::$id   = ApkDb::getInstance()->lastInsertId();
        self::$role = self::STANDARD;
    }

    private static function createGoogleUser($email, $name){
        self::$login = $name;
        $stmt        = ApkDb::getInstance()->prepare(self::$createGooglrUser);
        $stmt->bindValue(':login', self::$login);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        self::$id   = ApkDb::getInstance()->lastInsertId();
        self::$role = self::STANDARD;
    }

    public static function fetchInfo($login) {
        $stmt = ApkDb::getInstance()->prepare(self::$roleSQL);
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    private static function createUser($login) {
        $stmt = ApkDb::getInstance()->prepare(self::$createUserSQL);
        $stmt->bindValue(':login', $login);
        $stmt->execute();

        return ['id' => ApkDb::getInstance()->lastInsertId(), 'role' => self::STANDARD];
    }

    private static function parseRole($role) {
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

    public static function isReadOnly() {
        return self::$role == self::READ_ONLY;
    }

    public static function isModerator() {
        return in_array(self::$role, [self::MODERATOR, self::DEVELOPER]);
    }

    private static function updateLastLogin() {
        $stmt = ApkDb::getInstance()->prepare(self::$updateLastLoginSQL);
        $stmt->bindValue(':id', self::$id);
        $stmt->execute();
    }
}