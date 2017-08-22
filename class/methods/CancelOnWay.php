<?php
/** @api-call cancel */

namespace methods;

use errors\Codes;
use user\OnwayStatus;

class CancelOnWay extends MethodWithAuth
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
        OnwayStatus::setCancel($this->id);
        return ['ok'];
    }
}