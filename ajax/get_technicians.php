<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

global $DB;

$group_id = (int)($_GET['group_id'] ?? 0);

$result = [];

if ($group_id > 0) {

    $iterator = $DB->request([
        'SELECT' => [
            'glpi_users.id',
            'glpi_users.firstname',
            'glpi_users.realname'
        ],
        'FROM' => 'glpi_users',
        'INNER JOIN' => [
            'glpi_groups_users' => [
                'ON' => [
                    'glpi_users' => 'id',
                    'glpi_groups_users' => 'users_id'
                ]
            ]
        ],
        'WHERE' => [
            'glpi_groups_users.groups_id' => $group_id
        ],
        'ORDER' => 'glpi_users.firstname'
    ]);

    foreach ($iterator as $row) {

        $result[] = [
            'id'   => $row['id'],
            'name' => trim(
                $row['firstname'] . ' ' . $row['realname']
            )
        ];
    }
}

header('Content-Type: application/json');

echo json_encode($result);