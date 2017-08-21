<?php
/** @api-call getDetails */
namespace methods;

use core\MethodInterface;
use db\ApkDb;
use errors\Codes;

class GetDetails implements MethodInterface
{
    private $result = ['m' => [], 'v' => [], 'h' => [], 'u' => []];
    private $id;

    private static $messagesQuery   = 'SELECT id, id_user AS o, UNIX_TIMESTAMP(created) AS ut, text AS t FROM messages WHERE id_ent=:id';
    private static $volunteersQuery = 'SELECT id_user AS o, status AS s, UNIX_TIMESTAMP(timest) AS ut FROM onway WHERE id=:id';
    private static $historyQuery    = 'SELECT 
                                          id_user AS o, 
                                          UNIX_TIMESTAMP(timest) AS ut, 
                                          CASE `action`
                                            WHEN "acc_status_act" THEN "a"
                                            WHEN "acc_status_end" THEN "e"
                                            WHEN "acc_status_hide"  THEN "h"
                                            WHEN "ban" THEN "b"
                                            WHEN "create_mc_acc" THEN "c"
                                            WHEN "inplace"  THEN "i"
                                            WHEN "leave" THEN "l"
                                            WHEN "onway" THEN "o"
                                            WHEN "cancel" THEN "cl"
                                            ELSE "na"
                                          END AS a
                                        FROM history 
                                        WHERE id_ent=:id';
    private static $usersQuery      = 'SELECT id, login 
                                        FROM users 
                                        WHERE 1=1 
                                          AND id IN (SELECT id_user FROM messages WHERE id_ent=:id1
                                                      UNION ALL
                                                     SELECT id_user FROM onway WHERE id=:id2
                                                      UNION ALL
                                                     SELECT id_user FROM history WHERE id_ent=:id3)';

    /**
     * Method constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        if (empty($data['id'])) throw new \InvalidArgumentException('Invalid arguments', Codes::INVALID_ARGUMENTS);
        $this->id = $data['id'];
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        if ($this->id === 0) return $this->result;
        $this->fetchMessages();
        $this->fetchVolunteers();
        $this->fetchHistory();
        $this->fetchUsers();
        return $this->result;
    }

    private function fetchMessages()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$messagesQuery);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->result['m'][] = ['id' => (int)$row['id'], 'o' => (int)$row['o'], 'ut' => (int)$row['ut'], 't' => $row['t']];
        }
    }

    private function fetchVolunteers()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$volunteersQuery);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $this->result['v'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetchHistory()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$historyQuery);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->result['h'][] = ['o' => (int)$row['o'], 'ut' => (int)$row['ut'], 'a' => $row['a']];
        }
    }

    private function fetchUsers()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$usersQuery);
        $stmt->bindValue(':id1', $this->id, \PDO::PARAM_INT);
        $stmt->bindValue(':id2', $this->id, \PDO::PARAM_INT);
        $stmt->bindValue(':id3', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->result['u'][$row['id']] = $row['login'];
        }
    }
}