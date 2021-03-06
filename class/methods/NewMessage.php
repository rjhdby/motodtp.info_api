<?php
/** @api-call newMessage */
namespace methods;

use db\ApkDb;
use errors\Codes;
use user\User;

class NewMessage extends MethodWithAuth
{
    private $id;
    private $text;

    private static $insertSql         = 'INSERT INTO messages (id_ent, id_user, `text`) VALUES (:id, :owner, :text)';
    private static $updateModifiedSql = 'UPDATE entities SET modified = NOW() WHERE id=:id';

    /**
     * @param array $data
     *
     * l - login
     * p - password hash
     * id - accident id
     * t - message text
     */
    public function __construct($data)
    {
        parent::__construct($data);
        if (User::isReadOnly()) throw new \InvalidArgumentException("Read only", Codes::READ_ONLY);
        if (empty($data["id"]) || empty($data["t"])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);
        $this->id   = $data["id"];
        $this->text = $data["t"];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        $this->insertMessage();
        $this->updateModifiedTime();
        return ['ok'];
    }

    private function insertMessage()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$insertSql);
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':owner', User::$id);
        $stmt->bindValue(':text', $this->text);
        $stmt->execute();
    }

    private function updateModifiedTime()
    {
        $stmt = ApkDb::getInstance()->prepare(self::$updateModifiedSql);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
    }
}