<?php
/** @api-call ban */

namespace methods;

use db\ApkDb;
use errors\Codes;
use user\User;

class Ban extends MethodWithAuth
{
    private static $banSql = 'UPDATE users SET role="readonly" WHERE id=:id';
    private static $logSql = 'INSERT INTO httplog (request) VALUES (:request)';
    private        $id;
    private        $data;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        if (!User::isModerator()) throw new \InvalidArgumentException("Insufficient rights", Codes::INSUFFICIENT_RIGHTS);
        if (empty($data["id"])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);
        $this->id   = $data["id"];
        $this->data = json_encode($data, true);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$banSql);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
        $stmt = ApkDb::getInstance()->prepare(self::$logSql);
        $stmt->bindValue(':request', $this->data);
        $stmt->execute();
        return ['ok'];
    }
}