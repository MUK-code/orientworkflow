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
 * Assigns GLPI ticket groups and technicians from an already selected route.
 */
class PluginOrientworkflowAssignmentService
{
    /**
     * Assigns the configured group and applies the route assignment mode.
     *
     * @param array<string, mixed> $route
     */
    public function assign(int $ticketId, array $route): bool
    {
        try {
            $groupId = (int) ($route['group_id'] ?? 0);
            $mode = strtoupper((string) ($route['assignment_mode'] ?? 'FIXED'));

            if (!$this->isValidId($ticketId) || !$this->isValidId($groupId)) {
                $this->log('assignment.failed', [
                    'ticket_id' => $ticketId,
                    'group_id' => $groupId,
                    'reason' => 'A valid ticket and group are required.',
                ]);

                return false;
            }

            $this->log('assignment.mode', [
                'ticket_id' => $ticketId,
                'group_id' => $groupId,
                'mode' => $mode,
            ]);

            if (!$this->assignGroup($ticketId, $groupId)) {
                return false;
            }

            if ($mode === 'ROUND_ROBIN') {
                return $this->assignRoundRobin($ticketId, $groupId);
            }

            if ($mode !== 'FIXED') {
                $this->log('assignment.failed', [
                    'ticket_id' => $ticketId,
                    'group_id' => $groupId,
                    'reason' => 'Unsupported assignment mode.',
                    'mode' => $mode,
                ]);

                return false;
            }

            return $this->assignFixedTechnician($ticketId, (int) ($route['technician_id'] ?? 0));
        } catch (Throwable $exception) {
            $this->log('assignment.exception', [
                'ticket_id' => $ticketId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Assigns a group to a ticket unless that assignment already exists.
     */
    public function assignGroup(int $ticketId, int $groupId): bool
    {
        try {
            if (!$this->isValidId($ticketId) || !$this->isValidId($groupId)) {
                return false;
            }

            $criteria = [
                'tickets_id' => $ticketId,
                'groups_id' => $groupId,
                'type' => CommonITILActor::ASSIGN,
            ];
            $assignment = new Group_Ticket();

            if ($assignment->getFromDBByCrit($criteria)) {
                $this->log('assignment.group.duplicate_skipped', $criteria);

                return true;
            }

            $result = $assignment->add($criteria);
            if ($result === false) {
                $this->log('assignment.group.failed', $criteria);

                return false;
            }

            $this->log('assignment.group.assigned', $criteria);

            return true;
        } catch (Throwable $exception) {
            $this->log('assignment.group.exception', [
                'ticket_id' => $ticketId,
                'group_id' => $groupId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Assigns a fixed technician to a ticket unless that assignment already exists.
     */
    public function assignFixedTechnician(int $ticketId, int $technicianId): bool
    {
        try {
            if (!$this->isValidId($ticketId) || !$this->isValidId($technicianId)) {
                $this->log('assignment.technician.failed', [
                    'ticket_id' => $ticketId,
                    'technician_id' => $technicianId,
                    'reason' => 'A valid ticket and technician are required.',
                ]);

                return false;
            }

            $criteria = [
                'tickets_id' => $ticketId,
                'users_id' => $technicianId,
                'type' => CommonITILActor::ASSIGN,
            ];
            $assignment = new Ticket_User();

            if ($assignment->getFromDBByCrit($criteria)) {
                $this->log('assignment.technician.duplicate_skipped', $criteria);

                return true;
            }

            $result = $assignment->add($criteria);
            if ($result === false) {
                $this->log('assignment.technician.failed', $criteria);

                return false;
            }

            $this->log('assignment.technician.assigned', $criteria);

            return true;
        } catch (Throwable $exception) {
            $this->log('assignment.technician.exception', [
                'ticket_id' => $ticketId,
                'technician_id' => $technicianId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delegates a group technician selection to the round-robin service.
     */
    private function assignRoundRobin(int $ticketId, int $groupId): bool
    {
        try {
            $service = new PluginOrientworkflowRoundRobinService();
            $technicianId = $service->getNextTechnician($groupId);
            if ($technicianId === null) {
                $this->log('assignment.round_robin.failed', [
                    'ticket_id' => $ticketId,
                    'group_id' => $groupId,
                    'reason' => 'No eligible technician was found.',
                ]);

                return false;
            }

            $result = $this->assignFixedTechnician($ticketId, $technicianId);
            if ($result) {
                $result = $service->recordAssignment($groupId, $technicianId);
            }

            $this->log($result ? 'assignment.round_robin.assigned' : 'assignment.round_robin.failed', [
                'ticket_id' => $ticketId,
                'group_id' => $groupId,
                'technician_id' => $technicianId,
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->log('assignment.round_robin.exception', [
                'ticket_id' => $ticketId,
                'group_id' => $groupId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Determines whether an identifier can be passed to a GLPI relation object.
     */
    private function isValidId(int $identifier): bool
    {
        return $identifier > 0;
    }

    /**
     * Sends structured assignment events to the plugin logger when available.
     *
     * @param array<string, mixed> $context
     */
    private function log(string $event, array $context): void
    {
        if (class_exists('PluginOrientworkflowLogger')) {
            PluginOrientworkflowLogger::info($event, $context);

            return;
        }

        Toolbox::logInFile('orientworkflow', json_encode([
            'event' => $event,
            'context' => $context,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
