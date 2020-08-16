<?php
/**
 * Created by PhpStorm.
 * User: seedteam
 * Date: 16.08.20
 * Time: 14:43
 */
require_once 'modules/Users/User.php';
Class UserUtils {
    public function getUserbyEmail($email) {
        global $db;
        $email = strtoupper($email);
        $sql="
        SELECT id
        FROM `users`
        WHERE
        `id` IN (
          SELECT `bean_id`
          FROM `email_addr_bean_rel`
          WHERE
          `email_address_id` IN (
              SELECT `id`
              FROM `email_addresses`
              WHERE
              `email_address_caps` = '{$email}'
              AND `deleted` = '0'
          )
          AND `bean_module` = 'Users'
          AND deleted = '0'
        )
        AND `deleted`= '0'
        LIMIT 1
        ";
        $result = $db->getOne($sql,1);
        return $result;
    }
}