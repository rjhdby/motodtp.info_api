<?php
/** @api-call auth */

namespace methods;


use core\MethodInterface;
use errors\Codes;
use user\User;

class Auth implements MethodInterface
{
    private $login;
    private $passHash;

    private $vk;
    private $googleEmail;
    private $googleName;

    /**
     * @param array $data
     * l - login
     * p - password hash
     * d - device ID (unused)
     */
    public function __construct($data) {
        if (isset($data['v'])) {
            $this->vk = $data['v'];
        } else if (isset($data['g'])) {
            $this->googleEmail = $data['g'];
            $this->googleName  = $data['n'];
        } else if (isset($data['l']) && isset($data['p'])) {
            $this->login    = $data['l'];
            $this->passHash = $data['p'];
        } else {
            throw new \InvalidArgumentException("Wrong username or password", Codes::WRONG_CREDENTIALS);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke() {
        if ($this->vk !== null) {
            User::auth($this->vk);
        } else if ($this->googleEmail !== null) {
            User::authGoogle($this->googleEmail, $this->googleName);
        } else {
            User::auth($this->login, $this->passHash);
        }
        return ['id' => User::$id, 'r' => User::$role, 'l' => User::$login];
    }
}