<?php
/** @api-call getList */
namespace methods;

use core\MethodInterface;
use db\ApkDb;
use user\User;

class GetList implements MethodInterface
{
    protected      $showTest   = false;
    private        $result     = ['l' => [], 'u' => []];
    private        $age        = 24;
    private        $modified   = '2014-01-01 00:00:01';
    private        $minId      = 0;
    private static $listQuery  = 'SELECT
					t1.id
					, UNIX_TIMESTAMP(t1.created) AS ut
					, t1.address
					, t1.description
					, CASE t1.status
                        WHEN "acc_status_act" THEN "a"
                        WHEN "acc_status_dbl" THEN "d"
                        WHEN "acc_status_end"  THEN "e"
                        WHEN "acc_status_hide" THEN "h"
                        WHEN "acc_status_war" THEN "w"
                        ELSE "a"
                        END AS `status`
					, t1.owner
					, t1.lat
					, t1.lon
					, CASE t1.acc_type
                        WHEN "acc_b" THEN "b"
                        WHEN "acc_m" THEN "m"
                        WHEN "acc_m_a" THEN "ma"
                        WHEN "acc_m_m" THEN "mm"
                        WHEN "acc_m_p" THEN "mp"
                        WHEN "acc_s" THEN "s"
                        ELSE "o"
                        END AS type
					, CASE t1.medicine
                        WHEN "mc_m_d" THEN "d"
                        WHEN "mc_m_h" THEN "h"
                        WHEN "mc_m_l" THEN "l"
                        WHEN "mc_m_wo" THEN "wo"
                        ELSE "na"
                        END AS medicine
					,IFNULL(MAX(t2.id), 0) AS lm
					,COUNT(t2.id) AS mc
					, is_test
				FROM entities t1
				LEFT JOIN messages t2 ON t1.id=t2.id_ent
				WHERE 1=1
					AND t1.status != "acc_status_dbl"
					AND t1.id>=:minId
				GROUP BY t1.id';
    private static $minIdQuery = 'SELECT MIN(id) FROM entities WHERE NOW() < (DATE_ADD(starttime, INTERVAL :age HOUR)) AND modified > :modified';
    private static $usersQuery = 'SELECT id, login FROM users WHERE id IN (SELECT DISTINCT owner FROM entities WHERE id>=:minId)';

    /**
     * Method constructor.
     * @param array $data
     *
     * $data keys
     * a - age in hours
     * u - user id
     * i - incremental. Fetch events modified after last fetch
     */
    public function __construct($data)
    {
        $this->age      = isset($data['a']) ? intval($data['a']) : $this->age;
        $this->showTest = isset($data['test']);
        if (isset($data['i']) && isset($data['u'])) {
            $modified       = User::getLastUpdateTime($data['u']);
            $this->modified = $modified ? $modified : $this->modified;
            User::setLastUpdateTime($data['u']);
        }
        $this->minId = $this->fetchMinId();
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        if ($this->minId !== 0) {
            $this->result['l'] = $this->prepareList($this->fetchList());
            $this->result['u'] = $this->prepareUsers($this->fetchUsers());
        }
        return $this->result;
    }

    private function fetchList()
    {
        $stmt = ApkDb::getInstance()->prepare(static::$listQuery);
        $stmt->bindValue(':minId', $this->minId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected function prepareList(\PDOStatement $stmt)
    {
        $out = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!$this->showTest && $row['is_test'] == 1) continue;
            $out[] = [
                'id' => (int)$row['id'],
                'ut' => (int)$row['ut'],
                'a' => $row['address'],
                'd' => $row['description'],
                's' => $row['status'],
                'o' => (int)$row['owner'],
                'y' => (float)$row['lat'],
                'x' => (float)$row['lon'],
                't' => $row['type'],
                'm' => $row['medicine'],
                'lm' => (int)$row['lm'],
                'mc' => (int)$row['mc'],
            ];
        }
        return $out;
    }

    private function fetchMinId()
    {
        $stmt = ApkDb::getInstance()->prepare(static::$minIdQuery);
        $stmt->bindValue(':age', $this->age, \PDO::PARAM_INT);
        $stmt->bindValue(':modified', $this->modified, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_NUM);
        return $result ? $result[0] : 0;
    }

    private function fetchUsers()
    {
        $stmt = ApkDb::getInstance()->prepare(static::$usersQuery);
        $stmt->bindValue(':minId', $this->minId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected function prepareUsers(\PDOStatement $stmt)
    {
        $out = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $out[$row['id']] = $row['login'];
        }
        return $out;
    }
}