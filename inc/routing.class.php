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
    
public static function process(
    \Glpi\Form\AnswersSet $answers_set,
    array $created_items
): void {

    self::log("========== NEW REQUEST ==========");
    self::log("AnswerSet ID : " . $answers_set->getID());
    self::log("Created Items : " . count($created_items));
    $answers = json_decode(
        $answers_set->fields['answers'],
        true
    );
    

    if (!is_array($answers)) {
        self::log("Invalid JSON");
        return;
    }
    if (empty($answers)) {
    self::log("Answers JSON is empty");
    return;
}

    $ticketData = self::parseAnswers($answers);
    $ticketData['branch'] = self::resolveDropdownValue(
    'Branch',
    $ticketData['branch']
    );

    $ticketData['service'] = self::resolveDropdownValue(
        'Service',
        $ticketData['service']
    );

    // $ticketData['category'] = self::resolveDropdownValue(
    //     'IT Category',
    //     $ticketData['category']
    // );
    if ($ticketData['service'] === 'SAP Support') {

    $ticketData['category'] = self::resolveDropdownValue(
        'SAP Category',
        $ticketData['category']
    );

    } else {

    $ticketData['category'] = self::resolveDropdownValue(
        'IT Category',
        $ticketData['category']
    );

}

    self::log("Parsed Data:");

    $route = self::findRoute($ticketData);

    if ($route === null) {
        self::log("Routing Failed");
        return;
    }

    self::log("Routing Success");
    self::log("Assign Group ID = " . $route['group_id']);

    // $route = self::findRoute($ticketData);

    // if ($route === null) {
    //     self::log("Routing Failed");
    //     return;
    // }

    self::log("Routing Success");
    
    // self::assignGroup(
    // $created_items[0]->getID(),
    // $route
    // );
    $item = reset($created_items);

    self::log("BEFORE assignGroup");

    self::log("Item Class = " . get_class($item));

    self::log("Ticket ID = " . $item->getID());

    self::assignGroup(
        $item->getID(),
        $route
    );

    foreach ($ticketData as $key => $value) {
        self::log($key . " = " . $value);
    }
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
    $result = [
        'branch'      => '',
        'service'     => '',
        'category'    => '',
        'title'       => '',
        'description' => ''
    ];

    foreach ($answers as $answer) {

        if (!isset($answer['question_label'])) {
            continue;
        }

        $label = trim($answer['question_label']);

        switch ($label) {

            case 'Branch':
                $result['branch'] = $answer['raw_answer'];
                break;

            case 'Service':
                $result['service'] = $answer['raw_answer'];
                break;

            case 'IT Category':
                $result['category'] = $answer['raw_answer'];
                break;

            case 'SAP Category':
            $result['category'] = $answer['raw_answer'];
            break;

            case 'Title':
                $result['title'] = strip_tags(
                    $answer['raw_answer']
                );
                break;

            case 'Description':
                $result['description'] = strip_tags(
                    $answer['raw_answer']
                );
                break;
        }
    }

    return $result;
}

    /**
     * Dropdown Raw ID ko Label mein convert karega.
     */
    private static function resolveDropdownValue(
    string $question,
    string $rawAnswer
): ?string {

    global $DB;

    $iterator = $DB->request([
        'SELECT' => ['extra_data'],
        'FROM'   => 'glpi_forms_questions',
        'WHERE'  => [
            'name' => $question
        ],
        'LIMIT'  => 1
    ]);

    foreach ($iterator as $row) {

        $extra = json_decode($row['extra_data'], true);

        if (
            isset($extra['options']) &&
            isset($extra['options'][$rawAnswer])
        ) {
            return $extra['options'][$rawAnswer];
        }
    }

    return $rawAnswer;
}

    /**
     * Routes table mein matching route find karega.
     */
    private static function findRoute(array $data): ?array
{
    global $DB;

    self::log("Searching Route...");

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_orientworkflow_routes',
        'WHERE' => [
            'branch'    => $data['branch'],
            'service'   => $data['service'],
            'category'  => $data['category'],
            'is_active' => 1
        ],
        'LIMIT' => 1
    ]);

    foreach ($iterator as $row) {

        self::log("Route Found");

        self::log(
            "Route ID: {$row['id']} | Group ID: {$row['group_id']}"
        );

        return $row;
    }

    self::log("No Route Found");

    return null;
}

    /**
     * Ticket ko Group assign karega.
     */
    private static function assignGroup(
    int $ticketId,
    array $route
    ): bool {
    self::log("assignGroup() ENTERED");
    $ticket = new Ticket();

    if (!$ticket->getFromDB($ticketId)) {
        self::log("Ticket not found");
        return false;
    }

    $result = $ticket->update([
        'id' => $ticketId,
        'groups_id_assign' => (int)$route['group_id']
    ]);
    self::log("Update Result = " . var_export($result, true));
    self::log("groups_id_assign = " . $ticket->fields['groups_id_assign']);

    if ($result) {
        self::log("Group Assigned Successfully");
        self::log("Group ID = " . $route['group_id']);
        return true;
    }
    if (!$result) {
    self::log("Update failed");
    self::log(print_r($ticket->getErrors(), true));
    }

    self::log("Group Assignment Failed");

    return false;
}
}