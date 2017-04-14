<?php
/** @api-call create */

namespace methods;


use BadMethodCallException;
use core\MethodInterface;
use db\ApkDb;
use errors\Codes;
use Exception;
use PDO;
use user\User;

class NewAccident implements MethodInterface
{
    /*
                'a'  => $row['address'],
                'd'  => $row['description'],
                's'  => $row['statistics'],
                'y'  => (float) $row['lat'],
                'x'  => (float) $row['lon'],
                't'  => $row['type'],
                'm'  => $row['medicine'],
     */

    private static $prerequisites = ['l', 'p', 'a', 'd', 'y', 'x', 't', 'm', 's'];
    private static $typesList     = ['b' => 'acc_b', 'm' => 'acc_m', 'ma' => 'acc_m_a', 'mm' => 'acc_m_m', 'mp' => 'acc_m_p', 'o' => 'acc_o'];
    private static $damageList    = ['d' => 'mc_m_d', 'h' => 'mc_m_h', 'l' => 'mc_m_l', 'wo' => 'mc_m_wo', 'na' => 'mc_m_na'];
    const MODERATOR_TIMEOUT = 30;
    const STANDARD_TIMEOUT  = 1800;

    private $address;
    private $description;
    private $lat;
    private $lon;
    private $type;
    private $damage;
    private $statistic = false;

    /**
     * @param array $data
     * @throws Exception
     */
    public function __construct($data)
    {
        $this->checkPrerequisites($data);

    }

    /**
     * @param array $data
     * @throws Exception
     */
    private function checkPrerequisites($data)
    {
        foreach (self::$prerequisites as $requisite) {
            if (empty($data[$requisite])) throw new BadMethodCallException("Wrong parameters");
        }
        User::auth($data['l'], $data['p']);
        if (User::isReadOnly()) throw new Exception("User is readonly", Codes::USER_IS_READ_ONLY);
        $stmt = ApkDb::getInstance()->prepare('SELECT IFNULL(UNIX_TIMESTAMP(MAX(created)), 0) FROM entities WHERE owner=:owner');
        $stmt->execute([':owner' => User::$id]);
        $timestamp = implode('', $stmt->fetch(PDO::FETCH_ASSOC));
        $timeout   = User::$role == User::STANDARD ? self::STANDARD_TIMEOUT : self::MODERATOR_TIMEOUT;
        if ((time() - $timestamp) < $timeout) throw new Exception($timeout - time() + $timestamp, Codes::USER_IS_READ_ONLY);
    }

    private function prepareData($data)
    {
        $this->statistic   = isset($data['s']);
        $this->address     = $data['a'];
        $this->description = $data['d'];
        $this->type        = isset(self::$typesList[$data['t']]) ? self::$typesList[$data['t']] : self::$typesList['o'];
        $this->damage      = isset(self::$damageList[$data['t']]) ? self::$damageList[$data['t']] : self::$damageList['o'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}