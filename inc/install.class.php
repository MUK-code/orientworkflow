<?php

/**
 * -------------------------------------------------------------------------
 * Orient Workflow Plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * Installation Class
 *
 * Handles:
 *  - Plugin installation
 *  - Plugin uninstallation
 *  - Future database migrations
 *
 * @author Muhammad Usman Khalid
 * @license GPL v2+
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginOrientworkflowInstall
{
    /**
     * Install plugin
     *
     * @return bool
     */
    public static function install(): bool
    {
        global $DB;

        // Database tables will be created here
        // Example:
        self::createTables();

        return true;
    }

    /**
     * Uninstall plugin
     *
     * @return bool
     */
    public static function uninstall(): bool
    {
        global $DB;

        // Future cleanup
        // Example:
        // self::dropTables();

        return true;
    }

    /**
     * Create plugin database tables
     *
     * @return void
     */
    private static function createTables(): void
    {
        global $DB;

        // Branches Table
        $query = "
        CREATE TABLE IF NOT EXISTS `glpi_plugin_orientworkflow_branches` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_mod` DATETIME DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci;
        ";

        $DB->doQuery($query);

        // Services Table
        $query = "
        CREATE TABLE IF NOT EXISTS `glpi_plugin_orientworkflow_services` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_mod` DATETIME DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci;
        ";

        $DB->doQuery($query);

       // Categories Table
        $query = "
        CREATE TABLE IF NOT EXISTS `glpi_plugin_orientworkflow_categories` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `service_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_mod` DATETIME DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `service_id` (`service_id`),
            UNIQUE KEY `service_category` (`service_id`, `name`)
        ) ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci;
        ";

        $DB->doQuery($query);

        // Routes Table
        // ...

        // Settings Table
        // ...

        // Logs Table
    }

    /**
     * Remove plugin database tables
     *
     * @return void
     */
    private static function dropTables(): void
    {
        global $DB;

        // Drop tables in future.
    }
}