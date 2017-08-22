<?php
/** @api-call getAccident */
namespace methods;

use core\MethodInterface;
use db\ApkDb;
use errors\Codes;

class GetAccident implements MethodInterface
{
    private        $id;
    private        $result     = ['l' => [], 'u' => []];
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
				FROM
					entities t1
				LEFT JOIN messages t2 ON t1.id=t2.id_ent
				WHERE
					1=1
					AND t1.status != "acc_status_dbl"
					AND t1.id = :id
				GROUP BY t1.id';
    private static $usersQuery = 'SELECT id, login FROM users WHERE id IN (SELECT DISTINCT owner FROM entities WHERE id = :id)';

    /**
     * Method constructor.
     * @param array $data
     *
     * id - accident ID
     */
    public function __construct($data)
    {
        if (empty($data['id'])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        $this->fetchList();
        $this->fetchUsers();
        return $this->result;
    }

    private function fetchList()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$listQuery);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $row                 = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->result['l'][] = [
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
            'lm' => (int)$row['lm']
        ];
    }

    private function fetchUsers()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$usersQuery);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->result['u'][$row['id']] = $row['login'];
        }
    }
}