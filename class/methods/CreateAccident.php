<?php
/** @api-call createAccident */
namespace methods;


use db\ApkDb;
use errors\Codes;
use notifications\FireBase;
use user\User;

class CreateAccident extends MethodWithAuth
{
    const TEST = false;

    const MODERATOR_TIMEOUT = 30;
    const STANDARD_TIMEOUT  = 1800;
    private static $timeoutSql    = 'SELECT IFNULL(UNIX_TIMESTAMP(MAX(created)), 0) FROM entities WHERE `owner`=:owner';
    private static $addSql        = 'INSERT INTO entities 
                              (starttime, modified, `owner`, lat, lon, address, description, `status`, is_test, acc_type, medicine)
                              VALUES
                              (NOW(), NOW(), :owner, :lat, :lon, :address, :description, :status, :is_test, :acc_type, :medicine)';
    private static $logSql        = 'INSERT INTO httplog (request) VALUES (:request)';
    private        $prerequisites = ['a', 'd', 'y', 'x', 't', 'dm'];
    private static $typesList     = ['b' => 'acc_b', 'm' => 'acc_m', 'ma' => 'acc_m_a', 'mm' => 'acc_m_m', 'mp' => 'acc_m_p', 'o' => 'acc_o'];
    private static $damageList    = ['d' => 'mc_m_d', 'h' => 'mc_m_h', 'l' => 'mc_m_l', 'wo' => 'mc_m_wo', 'na' => 'mc_m_na'];

    private $data;
    private $id = 0;

    /*
                'a'  => $row['address'],
                'd'  => $row['description'],
                's'  => $row['statistics'],       ????????
                'y'  => (float) $row['lat'],
                'x'  => (float) $row['lon'],
                't'  => $row['type'],
                'm'  => $row['medicine'],
     */
    public function __construct($data)
    {
        parent::__construct($data);
        if (User::isReadOnly()) throw new \InvalidArgumentException("Read only", Codes::READ_ONLY);
        foreach ($this->prerequisites as $param) {
            if (empty($data[$param])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);
        }
        $this->checkTimeout();
        $this->data = $data;
    }

    private function checkTimeout()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$timeoutSql);
        $stmt->bindValue(':owner', User::$id);
        $stmt->execute();
        $result  = (int)$stmt->fetchColumn(0);
        $timeout = User::isModerator() ? self::MODERATOR_TIMEOUT : self::STANDARD_TIMEOUT;
        if ((time() - $result) < $timeout) throw new \Exception($timeout - time() + $result, Codes::CREATE_TIMEOUT);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        $this->insertAccident();
        $this->log();
        $this->push();
        $this->share();
        return ['id' => $this->id];
    }

    private function insertAccident()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$addSql);
        $stmt->bindValue(':owner', User::$id);
        $stmt->bindValue(':lat', $this->data['y']);
        $stmt->bindValue(':lon', $this->data['x']);
        $stmt->bindValue(':address', $this->data['a']);
        $stmt->bindValue(':description', $this->data['d']);
        $stmt->bindValue(':acc_type', self::$typesList[$this->data['t']]);
        $stmt->bindValue(':medicine', self::$damageList[$this->data['dm']]);
        $stmt->bindValue(':is_test', self::TEST || isset($this->data['test']) ? 1 : 0);
        $stmt->bindValue(':status', isset($this->data['s']) ? "acc_status_end" : "acc_status_act");
        $stmt->execute();
        $this->id = ApkDb::getInstance()->lastInsertId();

    }

    private function log()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$logSql);
        $stmt->bindValue(':request', json_encode($this->data, true));
        $stmt->execute();
    }

    private function push()
    {
        if ($this->id === 0 || isset($this->data['s'])) return;
        FireBase::sendBroadcast(['id' => (string)$this->id], isset($this->data['test']));
    }

    private function share()
    {
        //todo
    }
}