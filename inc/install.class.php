<?php

/**
 * Orient Workflow Plugin for GLPI.
 *
 * @copyright 2026 Muhammad Usman Khalid
 * @license GPL-2.0-or-later
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Installs, upgrades, and uninstalls Orient Workflow database objects.
 */
class PluginOrientworkflowInstall
{
    private const ROUTES_TABLE = 'glpi_plugin_orientworkflow_routes';

    /**
     * Creates the route table when needed and upgrades existing installations.
     */
    public static function install(): bool
    {
        try {
            self::createRoutesTable();
            self::migrateRoutesTable();

            return true;
        } catch (Throwable $exception) {
            self::logFailure('Installation failed: ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * Removes database objects owned by this plugin only.
     */
    public static function uninstall(): bool
    {
        try {
            self::executeQuery('DROP TABLE IF EXISTS `' . self::ROUTES_TABLE . '`');

            return true;
        } catch (Throwable $exception) {
            self::logFailure('Uninstallation failed: ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * Creates the complete schema for a fresh installation.
     */
    private static function createRoutesTable(): void
    {
        self::executeQuery(
            'CREATE TABLE IF NOT EXISTS `' . self::ROUTES_TABLE . '` ('
            . '`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . '`branch` VARCHAR(100) NOT NULL,'
            . '`service` VARCHAR(100) NOT NULL,'
            . '`category` VARCHAR(100) NOT NULL,'
            . '`group_id` INT UNSIGNED NOT NULL,'
            . '`technician_id` INT UNSIGNED DEFAULT NULL,'
            . "`assignment_mode` VARCHAR(20) NOT NULL DEFAULT 'FIXED',"
            . '`itilcategories_id` INT UNSIGNED DEFAULT NULL,'
            . '`priority` TINYINT UNSIGNED DEFAULT NULL,'
            . '`sla_id` INT UNSIGNED DEFAULT NULL,'
            . '`entity_id` INT UNSIGNED NOT NULL DEFAULT 0,'
            . '`is_active` TINYINT(1) NOT NULL DEFAULT 1,'
            . '`date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,'
            . '`date_mod` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . 'PRIMARY KEY (`id`),'
            . 'KEY `idx_orientworkflow_route_it` (`branch`, `service`, `category`, `is_active`),'
            . 'KEY `idx_orientworkflow_route_sap` (`service`, `category`, `is_active`),'
            . 'KEY `idx_orientworkflow_route_group` (`group_id`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Adds every schema element required by the current plugin version.
     * Existing rows and column values are never changed by this migration.
     */
    private static function migrateRoutesTable(): void
    {
        self::addColumnIfMissing(
            'assignment_mode',
            "VARCHAR(20) NOT NULL DEFAULT 'FIXED' AFTER `technician_id`"
        );
        self::addColumnIfMissing(
            'itilcategories_id',
            'INT UNSIGNED DEFAULT NULL AFTER `assignment_mode`'
        );

        self::addIndexIfMissing(
            'idx_orientworkflow_route_it',
            '(`branch`, `service`, `category`, `is_active`)'
        );
        self::addIndexIfMissing(
            'idx_orientworkflow_route_sap',
            '(`service`, `category`, `is_active`)'
        );
        self::addIndexIfMissing(
            'idx_orientworkflow_route_group',
            '(`group_id`)'
        );
    }

    /**
     * Adds a column only when an earlier plugin version does not contain it.
     */
    private static function addColumnIfMissing(string $column, string $definition): void
    {
        global $DB;

        if ($DB->fieldExists(self::ROUTES_TABLE, $column)) {
            return;
        }

        self::executeQuery(
            'ALTER TABLE `' . self::ROUTES_TABLE . '` ADD COLUMN `'
            . $column . '` ' . $definition
        );
    }

    /**
     * Adds a named index only once, making installation safely repeatable.
     */
    private static function addIndexIfMissing(string $name, string $columns): void
    {
        if (self::indexExists($name)) {
            return;
        }

        self::executeQuery(
            'ALTER TABLE `' . self::ROUTES_TABLE . '` ADD INDEX `'
            . $name . '` ' . $columns
        );
    }

    /**
     * Determines whether a named index already exists on the route table.
     */
    private static function indexExists(string $name): bool
    {
        global $DB;

        $result = $DB->doQuery(
            'SHOW INDEX FROM `' . self::ROUTES_TABLE . "` WHERE Key_name = '"
            . $name . "'"
        );

        if ($result === false) {
            throw new RuntimeException('Unable to inspect route table indexes.');
        }

        return $DB->numrows($result) > 0;
    }

    /**
     * Executes a schema statement and turns database failures into exceptions.
     */
    private static function executeQuery(string $query): void
    {
        global $DB;

        if ($DB->doQuery($query) === false) {
            throw new RuntimeException('Unable to update Orient Workflow database schema.');
        }
    }

    /**
     * Records installation failures without masking the original operation result.
     */
    private static function logFailure(string $message): void
    {
        $path = GLPI_ROOT . '/files/_log/orientworkflow.log';
        $entry = sprintf("%s [install] %s%s", date('Y-m-d H:i:s'), $message, PHP_EOL);

        @file_put_contents($path, $entry, FILE_APPEND | LOCK_EX);
    }
}
