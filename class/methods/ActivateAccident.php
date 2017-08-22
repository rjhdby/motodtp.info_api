<?php
/** @api-call activateAccident */

namespace methods;


use accidents\AccidentStatus;
use errors\Codes;
use user\User;

class ActivateAccident extends MethodWithAuth
{
    private $id;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        if (User::isReadOnly()) throw new \InvalidArgumentException("Read only", Codes::READ_ONLY);
        if (empty($data["id"])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);
        $this->id = $data["id"];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        AccidentStatus::setActive($this->id);
        return ['ok'];
    }
}