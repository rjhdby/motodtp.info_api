<?php
namespace user;

use db\ApkDb;

class OnwayStatus
{
    const ON_WAY   = 0;
    const IN_PLACE = 1;
    const LEAVE    = 2;
    const CANCEL   = 4;

    private static $volunteerStatusSQL = 'INSERT INTO onway (id, id_user, `status`) VALUES(:id, :owner, :status) ON DUPLICATE KEY UPDATE status=:newStatus, timest = NOW()';
    private static $updateAccidentSQL  = 'UPDATE entities SET modified = NOW() WHERE id=:id';
    private static $historySQL         = 'INSERT INTO history (id_ent, id_user, `action`) VALUES (:id, :owner, :action)';

    public static function setOnway($id)
    {
        self::changeStatus($id, 'onway');
    }

    public static function setInplace($id)
    {
        self::changeStatus($id, 'inplace');
    }

    public static function setLeave($id)
    {
        self::changeStatus($id, 'leave');
    }

    public static function setCancel($id)
    {
        self::changeStatus($id, 'cancel');
    }

    private static function changeStatus($id, $status)
    {
        $stmt = ApkDb::getInstance()->prepare(self::$volunteerStatusSQL);
        $stmt->execute([':id' => $id, ':owner' => User::$id, ':status' => $status, ':newStatus' => $status]);

        $stmt = ApkDb::getInstance()->prepare(self::$updateAccidentSQL);
        $stmt->execute([':id' => $id]);

        $stmt = ApkDb::getInstance()->prepare(self::$historySQL);
        $stmt->execute([':id' => $id, ':owner' => User::$id, ':action' => $status]);
    }
}