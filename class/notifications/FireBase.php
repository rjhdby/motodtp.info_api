<?php

namespace notifications;


use core\Config;
use db\ApkDb;
use PDO;

class FireBase
{
    private static $accidentQuery = 'SELECT t1.*, IFNULL(MAX(t2.id),0) AS lm FROM(SELECT
					id
					, UNIX_TIMESTAMP(created) AS ut
					, address
					, description
					, CASE status
                        WHEN "acc_status_act" THEN "a"
                        WHEN "acc_status_dbl" THEN "d"
                        WHEN "acc_status_end"  THEN "e"
                        WHEN "acc_status_hide" THEN "h"
                        WHEN "acc_status_war" THEN "w"
                        ELSE "a"
                        END AS `status`
					, owner
					, lat
					, lon
					, CASE acc_type
                        WHEN "acc_b" THEN "b"
                        WHEN "acc_m" THEN "m"
                        WHEN "acc_m_a" THEN "ma"
                        WHEN "acc_m_m" THEN "mm"
                        WHEN "acc_m_p" THEN "mp"
                        WHEN "acc_s" THEN "s"
                        ELSE "o"
                        END AS type
					, CASE medicine
                        WHEN "mc_m_d" THEN "d"
                        WHEN "mc_m_h" THEN "h"
                        WHEN "mc_m_l" THEN "l"
                        WHEN "mc_m_wo" THEN "wo"
                        ELSE "na"
                        END AS medicine
				FROM entities
				WHERE id=10868) t1
				LEFT JOIN messages t2 ON t1.id=t2.id_ent';

    public static function newAccident($id)
    {
        $stmt = ApkDb::getInstance()->prepare(self::$accidentQuery);
        $stmt->execute([':id' => $id]);
        $result  = $stmt->fetch(PDO::FETCH_ASSOC);
        $payload = [
            'id' => (int)$result['id'],
            'ut' => (int)$result['ut'],
            'a' => $result['address'],
            'd' => $result['description'],
            's' => $result['status'],
            'o' => (int)$result['owner'],
            'y' => (float)$result['lat'],
            'x' => (float)$result['lon'],
            't' => $result['type'],
            'm' => $result['medicine'],
            'lm' => (int)$result['lm']
        ];
        self::sendBroadcast($payload);
    }

    private static function sendBroadcast($payload)
    {
        $headers = array(
            'Authorization: key=' . Config::get('firebaseKey'),
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Config::get('firebaseUrl'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['data' => $payload, 'to' => '/topics/accidents']));

        $result = curl_exec($ch);
        var_dump($result);
        var_dump($payload);
    }
}