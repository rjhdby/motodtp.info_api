<?php
/** @api-call onway */

namespace methods;


use core\MethodInterface;
use db\ApkDb;
use errors\Codes;
use user\User;

class OnWay implements MethodInterface
{
    const ON_WAY   = 0;
    const IN_PLACE = 1;
    const LEAVE    = 2;
    const CANCEL   = 4;

    private $action;
    private $id;
    private $owner;

    private static $volunteerStatusSQL = 'INSERT INTO onway (id, id_user, status) VALUES(:id,:owner,:status) ON DUPLICATE KEY UPDATE status=:newStatus, timest = NOW()';
    private static $updateAccidentSQL  = 'UPDATE entities SET modified = NOW() WHERE id=:id';
    private static $historySQL         = 'INSERT INTO history (id_ent, id_user, action) VALUES (:id,:owner,:action)';

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        if (empty($data['a']) || empty($data['id']) || empty($data['l'])) throw new \InvalidArgumentException('Invalid arguments', Codes::INVALID_ARGUMENTS);
        $this->action = $data['a'];
        $this->id     = $data['id'];
        $user         = User::fetchInfo($data['l']);
        if ($user === false) throw new \InvalidArgumentException('Invalid arguments', Codes::INVALID_ARGUMENTS);
        $this->owner = $user['id'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        $this->updateOnWay();
        $this->updateAccident();
        $this->updateHistory();
        return ['r' => 'OK'];
    }

    private function updateOnWay()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$volunteerStatusSQL);
        $stmt->execute([':id' => $this->id, ':owner' => $this->owner, ':status' => $this->action, ':newStatus' => $this->action]);
    }

    private function updateAccident()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$updateAccidentSQL);
        $stmt->execute([':id' => $this->id]);
    }

    private function updateHistory()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$historySQL);
        $stmt->execute([':id' => $this->id, ':owner' => $this->owner, ':action' => $this->action]);
    }
}