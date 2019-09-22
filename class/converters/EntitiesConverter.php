<?php


namespace converters;


use PDO;

class EntitiesConverter extends AbstractConverter
{
    /**
     * EntitiesConverter constructor.
     */
    public function __construct($data) {
        $WHERE = 'WHERE 1=1';
        if (isset($data['from'])) {
            $WHERE .= ' AND id >= ' . ((int)$data['from']);
        }
        if (isset($data['until'])) {
            $WHERE .= ' AND id < ' . ((int)$data['until']);
        }
        parent::__construct("
        SELECT id, acc_type, UNIX_TIMESTAMP(created) AS created, owner, description, lon, lat, accuracy, address, is_test, status, medicine
        FROM entities
        $WHERE
        ");
    }


    /**
     * @return array
     */
    public function getConverted() {
        $content = $this->getContent();
        $out     = [];
        while ($row = $content->fetch(PDO::FETCH_ASSOC)) {
            $out[] = [
                "legacyId" => (int)$row['id'],
                "type" => $this->convertType($row['acc_type']),
                "created" => $row['created'],
                "legacyAuthor" => (int)$row['owner'],
                "location" => $this->convertLocation($row),
                "attributes" => $this->convertAttributes($row),
                "description" => $row['description']
            ];
        }
        return $out;
    }

    private function convertType($type) {
        switch ($type) {
            case 'acc_b':
                return 'break';
            case 'acc_s':
                return 'steal';
            case 'acc_o':
                return 'other';
            default:
                return 'accident';
        }
    }

    private function convertLocation(array $row) {
        return [
            'latitude' => (float)$row['lat'],
            'longitude' => (float)$row['lon'],
            'accuracy' => (float)$row['accuracy'],
            'address' => $row['address']
        ];
    }

    private function convertAttributes(array $row) {
        return [
            'test' => ($row['is_test'] == 1),
            'hidden' => ($row['status'] == 'acc_status_hide'),
            'conflict' => ($row['status'] == 'acc_status_war'),
            'active' => ($row['status'] == 'acc_status_act'),
            'subtype' => $this->convertSubtype($row['acc_type']),
            'impact' => $this->convertImpact($row['medicine']),
        ];
    }

    private function convertSubtype($type) {
        switch ($type) {
            case 'acc_m' :
                return 'solo';
            case 'acc_m_m':
                return 'moto_moto';
            case 'acc_m_a':
                return 'moto_car';
            case 'acc_m_p':
                return 'moto_man';
            default:
                return 'unknown';
        }
    }

    private function convertImpact($type) {
        switch ($type) {
            case 'mc_m_na':
                return 'unknown';
            case 'mc_m_wo':
                return 'no_impact';
            case 'mc_m_l':
                return 'light';
            case 'mc_m_h':
                return 'heavy';
            case 'mc_m_d':
                return 'lethal';
            default:
                return 'wrong';
        }
    }
}