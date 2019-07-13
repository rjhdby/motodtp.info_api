<?php
/** @api-call HasNew */
namespace methods;


use core\MethodInterface;
use db\ApkDb;
use errors\Codes;

class HasNew implements MethodInterface
{
    private static $sql = 'SELECT UNIX_TIMESTAMP(MAX(modified)) FROM entities';
    private        $time;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        if (empty($data['ut'])) throw new \InvalidArgumentException('Invalid arguments', Codes::INVALID_ARGUMENTS);
        $this->time = (int)$data['ut'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$sql);
        $stmt->execute();
        $result = (int)$stmt->fetchColumn();
        return $result > $this->time ? ['y'] : ['n'];
    }
}