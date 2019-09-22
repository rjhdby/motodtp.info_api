<?php
namespace accidents;

use db\ApkDb;
use user\User;

class AccidentStatus
{
    const ACTIVE = 'acc_status_act';
    const ENDED  = 'acc_status_end';
    const HIDDEN = 'acc_status_hide';
    const DOUBLE = 'acc_status_dbl';

    private static $sql     = 'UPDATE entities SET `status`=:status WHERE id=:id';
    private static $history = 'INSERT INTO history (id_ent, id_user, action) VALUES (:id, :user, :status)';

    private static function changeStatus($id, $status)
    {
        $stmt = ApkDb::getInstance()->prepare(self::$sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $stmt->closeCursor();
        $stmt = ApkDb::getInstance()->prepare(self::$history);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':user', User::$id);
        $stmt->execute();
        $stmt->closeCursor();
    }

    public static function setEnded($id)
    {
        self::changeStatus($id, self::ENDED);
    }

    public static function setActive($id)
    {
        self::changeStatus($id, self::ACTIVE);
    }

    public static function setHidden($id)
    {
        self::changeStatus($id, self::HIDDEN);
    }

    public static function setDouble($id)
    {
        self::changeStatus($id, self::DOUBLE);
    }
}