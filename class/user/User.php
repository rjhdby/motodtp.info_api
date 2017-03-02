<?php

namespace user;

use db\ApkDb;

class User
{
    public static function getLastUpdateTime ($id) {
        if ($id === 0) return false;
        $stmt = ApkDb::getInstance()->prepare('SELECT IFNULL(lastgetlist,DATE_SUB(NOW(), INTERVAL 24 HOUR)) FROM users WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_NUM);
        return $result ? $result[0] : false;
    }

    public static function setLastUpdateTime ($id) {
        if ($id === 0) return;
        $stmt = ApkDb::getInstance()->prepare('UPDATE users SET lastgetlist = NOW() WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
}