<?php
/** @api-call hideAccident */

namespace methods;


use accidents\AccidentStatus;
use errors\Codes;
use user\User;

class HideAccident extends MethodWithAuth
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
        AccidentStatus::setHidden($this->id);
        return ['ok'];
    }
}