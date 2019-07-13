<?php
/** @api-call createuser */

namespace methods;


use db\ApkDb;
use errors\Codes;
use user\User;

class CreateUser extends MethodWithAuth
{
    private static $sql = "INSERT INTO users(login, role, hash, salt) VALUES (:login, 'moderator', :hash, :salt);";
    private        $login;

    /**
     * @param array $data
     */
    public function __construct ($data) {
        parent::__construct($data);
        if (!User::isModerator()) throw new \InvalidArgumentException("Insufficient rights", Codes::INSUFFICIENT_RIGHTS);
        if (empty($data["login"])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);


        $this->login = $data["login"];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke () {
        $pass = $this->readableRandomString();
        $salt = substr(base64_encode(uniqid(mt_rand())), 0, 5);

        $hash = md5(md5($salt) . md5($pass));

        $stmt = ApkDb::getInstance()->prepare(self::$sql);
        $stmt->bindValue(':login', $this->login);
        $stmt->bindValue(':hash', $hash);
        $stmt->bindValue(':salt', $salt);
        $stmt->execute();

        return [$pass];
    }

    private function readableRandomString ($length = 6) {
        $string     = '';
        $vowels     = ["a", "e", "i", "o", "u"];
        $consonants = [
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
        ];
        // Seed it
        srand((double) microtime() * 1000000);
        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++) {
            $string .= $consonants[rand(0, 19)];
            $string .= $vowels[rand(0, 4)];
        }
        return $string;
    }
}