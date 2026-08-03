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
 * Selects the next eligible technician for a GLPI group using persistent rotation state.
 */
class PluginOrientworkflowRoundRobinService
{
    private const CONFIG_CONTEXT = 'plugin:orientworkflow';

    /**
     * Returns the next active, non-deleted technician assigned to the group.
     * The selected technician is persisted as the group's last assignment so
     * the sequence continues after process and server restarts.
     */
    public function getNextTechnician(int $groupId): ?int
    {
        Toolbox::logInFile(
        'orientworkflow',
        "GET NEXT TECHNICIAN CALLED\n"
        );
        try {
            if ($groupId <= 0) {
                $this->log('round_robin.selection.failed', [
                    'group_id' => $groupId,
                    'reason' => 'A valid group identifier is required.',
                ]);

                return null;
            }

            $technicianIds = $this->getEligibleTechnicianIds($groupId);
            if ($technicianIds === []) {
                $this->log('round_robin.no_technician_found', ['group_id' => $groupId]);

                return null;
            }

            $previousTechnicianId = $this->getLastTechnicianId($groupId);
            $nextTechnicianId = $this->selectNextTechnician($technicianIds, $previousTechnicianId);

            if (!in_array($previousTechnicianId, $technicianIds, true)) {
                $this->log('round_robin.rotation_reset', [
                    'group_id' => $groupId,
                    'previous_technician_id' => $previousTechnicianId,
                    'technician_id' => $nextTechnicianId,
                ]);
            }

            $this->log('round_robin.technician_selected', [
                'group_id' => $groupId,
                'previous_technician_id' => $previousTechnicianId,
                'technician_id' => $nextTechnicianId,
            ]);

            return $nextTechnicianId;
        } catch (Throwable $exception) {
            $this->log('round_robin.exception', [
                'group_id' => $groupId,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Persists a technician after the ticket assignment has succeeded.
     */
    public function recordAssignment(int $groupId, int $technicianId): bool
    {
        Toolbox::logInFile(
        'orientworkflow',
        "RECORD ASSIGNMENT CALLED\n"
        );
        try {
            if ($groupId <= 0 || $technicianId <= 0) {
                return false;
            }

            $this->persistLastTechnicianId($groupId, $technicianId);
            $this->log('round_robin.assignment_recorded', [
                'group_id' => $groupId,
                'technician_id' => $technicianId,
            ]);

            return true;
        } catch (Throwable $exception) {
            $this->log('round_robin.persistence_failed', [
                'group_id' => $groupId,
                'technician_id' => $technicianId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Gets enabled, non-deleted users assigned to the supplied GLPI group.
     *
     * @return list<int>
     */
    private function getEligibleTechnicianIds(int $groupId): array
    {
        $technicianIds = [];

        foreach (Group_User::getGroupUsers($groupId) as $groupUser) {
            $userId = (int) ($groupUser['id'] ?? 0);
            if ($userId > 0 && $this->isEligibleTechnician($userId)) {
                $technicianIds[] = $userId;
            }
        }

        $technicianIds = array_values(array_unique($technicianIds));
        sort($technicianIds, SORT_NUMERIC);

        return $technicianIds;
    }

    /**
     * Determines whether a GLPI user is available for automated assignment.
     */
    private function isEligibleTechnician(int $userId): bool
    {
        $user = new User();

        if (!$user->getFromDB($userId)) {
            return false;
        }

        if ((int) ($user->fields['is_deleted'] ?? 0) === 1) {
            return false;
        }

        return (int) ($user->fields['is_active'] ?? 1) === 1;
    }

    /**
     * Retrieves the last persisted technician selected for a group.
     */
    private function getLastTechnicianId(int $groupId): int
    {
        $values = Config::getConfigurationValues(self::CONFIG_CONTEXT);

        return (int) ($values[$this->getConfigKey($groupId)] ?? 0);
    }

    /**
     * Chooses the technician immediately after the previous one in stable ID order.
     *
     * @param list<int> $technicianIds
     */
    private function selectNextTechnician(array $technicianIds, int $previousTechnicianId): int
    {
        $previousIndex = array_search($previousTechnicianId, $technicianIds, true);
        if ($previousIndex === false) {
            return $technicianIds[0];
        }

        return $technicianIds[($previousIndex + 1) % count($technicianIds)];
    }

    /**
     * Persists the last selected technician through GLPI's plugin configuration API.
     */
    private function persistLastTechnicianId(int $groupId, int $technicianId): void
    {
        Config::setConfigurationValues(self::CONFIG_CONTEXT, [
            $this->getConfigKey($groupId) => (string) $technicianId,
        ]);
    }

    /**
     * Builds a unique configuration key for one group's rotation state.
     */
    private function getConfigKey(int $groupId): string
    {
        return 'round_robin_last_technician_group_' . $groupId;
    }

    /**
     * Sends structured round-robin events to the plugin logger when available.
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
