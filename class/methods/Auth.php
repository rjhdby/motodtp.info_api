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

    private $device;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        if (empty($data['l']) || empty($data['p'])) throw new \InvalidArgumentException("Wrong username or password", Codes::WRONG_CREDENTIALS);
        $this->login    = $data['l'];
        $this->passHash = $data['p'];
        $this->device   = empty($data['d']) ? '' : $data['d'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        User::auth($this->login, $this->passHash);
        return ['id' => User::$id, 'r' => User::$role];
    }
}