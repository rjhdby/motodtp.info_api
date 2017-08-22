<?php
namespace user;

use db\ApkDb;

class OnwayStatus
{
    const ON_WAY   = 'onway';
    const IN_PLACE = 'inplace';
    const LEAVE    = 'leave';
    const CANCEL   = 'cancel';

    private static $volunteerStatusSQL = 'INSERT INTO onway (id, id_user, `status`) VALUES(:id, :owner, :status) ON DUPLICATE KEY UPDATE status=:newStatus, timest = NOW()';
    private static $updateAccidentSQL  = 'UPDATE entities SET modified = NOW() WHERE id=:id';
    private static $historySQL         = 'INSERT INTO history (id_ent, id_user, `action`) VALUES (:id, :owner, :action)';

    public static function setOnway($id)
    {
        self::changeStatus($id, self::ON_WAY);
    }

    public static function setInPlace($id)
    {
        self::changeStatus($id, self::IN_PLACE);
    }

    public static function setLeave($id)
    {
        self::changeStatus($id, self::LEAVE);
    }

    public static function setCancel($id)
    {
        self::changeStatus($id, self::CANCEL);
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