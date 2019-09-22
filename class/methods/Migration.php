<?php
/** @api-call Migration */

namespace methods;


use converters\Converter;
use converters\EntitiesConverter;
use core\MethodInterface;
use RuntimeException;

class Migration implements MethodInterface
{
    private $table;
    private $data;

    /**
     * @param array $data
     */
    public function __construct($data) {
        if (!isset($data['table']))
            throw new RuntimeException("'table' parameter missed");
        $this->table = $data['table'];
        if (!isset(self::$converters[$this->table]))
            throw new RuntimeException("No converter for table $this->table");
        $this->data = $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __invoke() {
        /** @var Converter $converter */
        $converter = new self::$converters[$this->table]($this->data);
        return $converter->getConverted();
    }

    private static $converters = [
        'entities' => EntitiesConverter::class
    ];
}