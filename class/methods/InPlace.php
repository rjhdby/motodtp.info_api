<?php
/** @api-call inPlace */

namespace methods;

use core\MethodInterface;
use errors\Codes;
use user\OnwayStatus;

class InPlace extends MethodWithAuth
{
    private $id;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        if (empty($data["id"])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);

        $this->id = $data['id'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        OnwayStatus::setInPlace($this->id);
        return ['ok'];
    }
}