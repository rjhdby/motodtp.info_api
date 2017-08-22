<?php
/** @api-call cancel */

namespace methods;

use core\MethodInterface;
use errors\Codes;
use user\OnwayStatus;

class CancelOnWay implements MethodInterface
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
        OnwayStatus::setCancel($this->id);
        return ['ok'];
    }
}