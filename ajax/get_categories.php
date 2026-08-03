<?php

/**
 * Orient Workflow Plugin for GLPI.
 *
 * Returns the supported routing categories for a selected service.
 *
 * @copyright 2026 Muhammad Usman Khalid
 * @license GPL-2.0-or-later
 */

include '../../../inc/includes.php';

Session::checkLoginUser();

header('Content-Type: application/json; charset=UTF-8');

$categoriesByService = [
    'IT Support' => [
        'Hardware',
        'Software',
        'Printer',
        'Email',
        'Internet',
        'VPN',
        'Access Request',
    ],
    'SAP Support' => [
        'SAP ABAP',
        'SAP Basis',
        'SAP FI',
        'SAP MM',
        'SAP PP',
        'SAP SD',
        'SAP HCM',
    ],
];

$service = trim((string) ($_GET['service'] ?? ''));
$categories = $categoriesByService[$service] ?? [];

try {
    echo json_encode(
        array_map(
            static fn(string $category): array => ['id' => $category, 'name' => $category],
            $categories
        ),
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (JsonException $exception) {
    http_response_code(500);
    echo '[]';
}
