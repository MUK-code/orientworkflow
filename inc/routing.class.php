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
     * Main Routing Engine
     * Ticket create hone ke baad ye method call hoga.
     */
    // public static function processTicket(int $ticketId): void
    // {
    //     // TODO: Step 1
    // // $answerSetId = self::getAnswerSetId($ticketId);

    // // if ($answerSetId === null) {
    // //     return;
    // // }

    // // $answers = self::getAnswers($answerSetId);

    // // if (empty($answers)) {
    // //     return;
    // // }

    // // $ticketData = self::parseAnswers($answers);

    // // if (empty($ticketData)) {
    // //     return;
    // // }

    // // $route = self::findRoute($ticketData);

    // // if ($route === null) {
    // //     return;
    // // }

    // // self::assignGroup($ticketId, $route);
    // // $answerSetId = self::getAnswerSetId($ticketId);

    // // echo "<pre>";
    // // var_dump($answerSetId);
    // // echo "</pre>";
    // // die();
    // echo "<h1 style='color:red'>Routing Engine Working</h1>";
    // echo "<br>Ticket ID : " . $ticketId;
    // die();

    // }

//     public static function processTicket(int $ticketId): void
// {
//     file_put_contents(
//         GLPI_ROOT . "/files/_log/orientworkflow.log",
//         date('Y-m-d H:i:s') . " - processTicket() called for Ticket ID: {$ticketId}\n",
//         FILE_APPEND
//     );

//     echo "<h1 style='color:red'>Routing Engine Working</h1>";
//     echo "<br>Ticket ID : " . $ticketId;
//     die();
// }

public static function processTicket(int $ticketId): void
{
    self::log("processTicket() called for Ticket ID: $ticketId");

    $answerSetId = self::getAnswerSetId($ticketId);

    self::log("AnswerSet ID: " . var_export($answerSetId, true));

    if (!$answerSetId) {
        return;
    }

    $answers = self::getAnswers($answerSetId);

    self::log(print_r($answers, true));
}

    /**
     * Forms Answer Set ID hasil karega.
     */
    private static function getAnswerSetId(int $ticketId): ?int
    {
        // TODO: Step 2
    global $DB;
    self::log("Searching AnswerSet for Ticket ID: $ticketId");
    $iterator = $DB->request([
        'SELECT' => ['forms_answerssets_id'],
        'FROM'   => 'glpi_forms_destinations_answerssets_formdestinationitems',
        'WHERE'  => [
            'itemtype' => 'Ticket',
            'items_id' => $ticketId
        ],
        'LIMIT' => 1
    ]);

    foreach ($iterator as $row) {
        return (int) $row['forms_answerssets_id'];
    }
    self::log("No AnswerSet Found");

    return null;
    }

    /**
     * Forms ke answers JSON read karega.
     */
    private static function getAnswers(int $answerSetId): array
    {
        // TODO: Step 3
    }

    private static function log(string $message): void
    {
        file_put_contents(
            GLPI_ROOT . "/files/_log/orientworkflow.log",
            date('Y-m-d H:i:s') . " - " . $message . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * JSON ko parse karega.
     */
    private static function parseAnswers(array $answers): array
    {
        // TODO: Step 4
    }

    /**
     * Dropdown Raw ID ko Label mein convert karega.
     */
    private static function resolveDropdownValue(
        string $question,
        string $rawAnswer
    ): ?string
    {
        // TODO: Step 5
    }

    /**
     * Routes table mein matching route find karega.
     */
    private static function findRoute(array $data): ?array
    {
        // TODO: Step 6
    }

    /**
     * Ticket ko Group assign karega.
     */
    private static function assignGroup(
        int $ticketId,
        array $route
    ): bool
    {
        // TODO: Step 7
    }
}