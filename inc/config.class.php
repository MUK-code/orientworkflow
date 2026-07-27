<?php

/**
 * -------------------------------------------------------------------------
 * Orient Workflow Plugin for GLPI
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginOrientworkflowConfig extends CommonDBTM
{
    public static function getTypeName($nb = 0)
    {
        return __('Orient Workflow', 'orientworkflow');
    }
}