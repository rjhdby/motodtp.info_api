<?php
/** @api-call newMessage */
namespace methods;

use core\MethodInterface;
use db\ApkDb;
use errors\Codes;
use user\User;

class NewMessage implements MethodInterface
{
    private $id;
    private $text;

    private static $sql = 'INSERT INTO messages (id_ent, id_user, `text`) VALUES (:id, :owner, :text)';

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
        $auth = new Auth($data);
        $auth();
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
        $stmt = ApkDb::getInstance()->prepare(self::$sql);
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':owner', User::$id);
        $stmt->bindValue(':text', $this->text);
        $stmt->execute();
        return ['ok'];
    }
}