<?php
/** @api-call leave */

namespace methods;

use core\MethodInterface;
use errors\Codes;
use user\OnwayStatus;

class Leave implements MethodInterface
{
    private $id;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $auth = new Auth($data);
        $auth();
        if (empty($data["id"])) throw new \InvalidArgumentException("Invalid arguments", Codes::INVALID_ARGUMENTS);

        $this->id = $data['id'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke()
    {
        OnwayStatus::setLeave($this->id);
        return ['ok'];
    }
}