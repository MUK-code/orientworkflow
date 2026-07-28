<?php

/**
 * -------------------------------------------------------------------------
 * Orient Workflow Plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * Hook File
 *
 * This file receives GLPI events and forwards them
 * to the Routing Engine.
 *
 * @author Muhammad Usman Khalid
 * @license GPL v2+
 * -------------------------------------------------------------------------
 */

<?php

function plugin_orientworkflow_ticket_add(CommonDBTM $item)
{
    file_put_contents(
        GLPI_ROOT . "/files/_log/orientworkflow.log",
        "plugin_orientworkflow_ticket_add called\n",
        FILE_APPEND
    );

    if (!$item instanceof Ticket) {
        return;
    }

    PluginOrientworkflowRouting::processTicket((int)$item->fields['id']);
}