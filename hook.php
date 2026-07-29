<?php

/**
 * Orient Workflow Plugin for GLPI
 *
 * Hook File
 *
 * This file receives GLPI events and forwards them
 * to the Routing Engine.
 *
 * @author Muhammad Usman Khalid
 * @license GPL v2+
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

require_once __DIR__ . '/inc/routing.class.php';

function plugin_orientworkflow_ticket_add(CommonDBTM $item)
{
    if (!$item instanceof Ticket) {
        return;
    }

    // sleep(2);

    // PluginOrientworkflowRouting::processTicket((int)$item->fields['id']);
    // return;
}