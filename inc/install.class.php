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
        // self::createTables();

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

        // SQL tables will be added here in next phase.
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