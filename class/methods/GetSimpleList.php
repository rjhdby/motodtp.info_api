<?php
/** @api-call getSimpleList */

namespace methods;


use core\MethodInterface;
use db\ApkDb;

class GetSimpleList implements MethodInterface
{
    private $query = 'SELECT
					a.id,
					UNIX_TIMESTAMP(a.created) AS uxtime,
					a.address,
					a.description,
					a.status,
					a.lat,
					a.lon,
					a.acc_type AS type,
					a.medicine
				FROM
					entities a
				WHERE NOW() < (DATE_ADD(a.starttime, INTERVAL 24 HOUR))';

    /**
     * Method constructor.
     * @param array $data
     */
    public function __construct ($data = null) {

    }

    /**
     * @return array
     */
    public function __invoke () {
        $stmt = ApkDb::getInstance()->prepare($this->query);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
}