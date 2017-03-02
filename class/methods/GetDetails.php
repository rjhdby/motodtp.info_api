<?php
/** @api-call getDetails */
namespace methods;

use core\MethodInterface;
use db\ApkDb;

class GetDetails implements MethodInterface
{
    private $result = ['m' => [], 'v' => [], 'h' => [], 'u' => []];
    private $id;

    private static $messagesQuery   = 'SELECT id, id_user AS o, UNIX_TIMESTAMP(a.modified) AS ut, text AS t FROM messages WHERE id_ent=:id';
    private static $volunteersQuery = 'SELECT id_user AS o, status AS s, UNIX_TIMESTAMP(timest) AS ut FROM onway WHERE id=:id';
    private static $historyQuery    = '';
    private static $usersQuery      = 'SELECT id, login 
                                        FROM users 
                                        WHERE 1=1 
                                          AND id IN (SELECT id_user FROM messages WHERE id_ent=:id1
                                                      UNION ALL
                                                     SELECT id_user FROM onway WHERE id=:id2)';

    /**
     * Method constructor.
     * @param array $data
     */
    public function __construct ($data) {
        $this->id = isset($data['id']) ? intval($data['id']) : 0;
    }

    /**
     * @return array
     */
    public function __invoke () {
        if ($this->id === 0) return $this->result;
        $this->fetchMessages();
        $this->fetchVolunteers();
        $this->fetchHistory();
        $this->fetchUsers();
        return $this->result;
    }

    private function fetchMessages () {
        $stmt = ApkDb::getInstance()->prepare(self::$messagesQuery);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $this->result['m'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetchVolunteers () {
        $stmt = ApkDb::getInstance()->prepare(self::$volunteersQuery);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $this->result['v'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //stub
    private function fetchHistory () {
        $this->result['h'] = [];
//        $stmt = ApkDb::getInstance()->prepare(self::$historyQuery);
//        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
//        $stmt->execute();
//        $this->result['h'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetchUsers () {
        $stmt = ApkDb::getInstance()->prepare(self::$usersQuery);
        $stmt->bindValue(':id1', $this->id, \PDO::PARAM_INT);
        $stmt->bindValue(':id2', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->result['u'][$row['id']] = $row['login'];
        }
    }
}