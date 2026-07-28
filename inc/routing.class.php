<?php

/**
 * -------------------------------------------------------------------------
 * Orient Workflow Plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * Routing Engine
 *
 * Responsible for:
 *  - Reading GLPI Forms answers
 *  - Parsing Branch / Service / Category
 *  - Finding matching route
 *  - Returning routing information
 *
 * @author Muhammad Usman Khalid
 * @license GPL v2+
 * -------------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginOrientworkflowRouting
{
    /**
     * Main entry point
     */
    public static function processTicket(int $ticketId): void
    {
        $answerSetId = self::getAnswerSetId($ticketId);
        echo "<pre>";
        var_dump($answerSetId);
        echo "</pre>";
        die();
        // Next Sprint
        // $answers = self::getFormAnswers($answerSetId);
    }

    /**
     * Get GLPI Forms AnswerSet ID from Ticket ID
     */
    private static function getAnswerSetId(int $ticketId): ?int
    {
        global $DB;

        $sql = "
            SELECT forms_answerssets_id
            FROM glpi_forms_destinations_answerssets_formdestinationitems
            WHERE itemtype = 'Ticket'
              AND items_id = ?
            LIMIT 1
        ";

        $iterator = $DB->request([
            'SELECT' => ['forms_answerssets_id'],
            'FROM'   => 'glpi_forms_destinations_answerssets_formdestinationitems',
            'WHERE'  => [
                'itemtype' => 'Ticket',
                'items_id' => $ticketId
            ],
            'LIMIT'  => 1
        ]);

        foreach ($iterator as $row) {
            return (int)$row['forms_answerssets_id'];
        }

        return null;
    }
}