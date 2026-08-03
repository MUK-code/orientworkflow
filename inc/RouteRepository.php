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
 * Provides persistence operations for Orient Workflow routing rules.
 */
class PluginOrientworkflowRouteRepository
{
    private const TABLE = 'glpi_plugin_orientworkflow_routes';

    /** @var list<string> */
    private const WRITABLE_FIELDS = [
        'branch',
        'service',
        'category',
        'group_id',
        'technician_id',
        'assignment_mode',
        'itilcategories_id',
        'priority',
        'sla_id',
        'entity_id',
        'is_active',
    ];

    /**
     * Finds the active route for the supplied Form values.
     *
     * IT Support routes are matched by branch, service, and category. SAP Support
     * routes are matched by service and category, regardless of branch.
     *
     * If duplicate rules exist, a fixed assignment containing a technician is
     * selected first. A round-robin rule is selected next because its selected
     * group is the source of eligible technicians.
     *
     * @return array<string, mixed>|null
     *
     * @throws RuntimeException When the database request cannot be completed.
     */
    public function findRoute(string $branch, string $service, string $category): ?array
    {
        $branch = trim($branch);
        $service = trim($service);
        $category = trim($category);

        if ($service === '' || $category === '' || ($service !== 'SAP Support' && $branch === '')) {
            $this->log('route.lookup.skipped', [
                'branch' => $branch,
                'service' => $service,
                'category' => $category,
                'reason' => 'Incomplete routing criteria.',
            ]);

            return null;
        }

        $criteria = [
            'service' => $service,
            'category' => $category,
            'is_active' => 1,
        ];

        if ($service !== 'SAP Support') {
            $criteria['branch'] = $branch;
        }

        try {
            global $DB;

            $routes = [];
            foreach ($DB->request([
                'FROM' => self::TABLE,
                'WHERE' => $criteria,
                'ORDER' => ['id DESC'],
            ]) as $route) {
                $routes[] = $route;
            }
        } catch (Throwable $exception) {
            $this->log('route.lookup.failed', [
                'branch' => $branch,
                'service' => $service,
                'category' => $category,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Unable to find the matching routing rule.', 0, $exception);
        }

        $route = $this->selectPreferredRoute($routes);
        $this->log('route.lookup.completed', [
            'branch' => $branch,
            'service' => $service,
            'category' => $category,
            'route_id' => $route['id'] ?? null,
            'assignment_mode' => $route['assignment_mode'] ?? null,
        ]);

        return $route;
    }

    /**
     * Creates a routing rule.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException When the supplied rule is invalid.
     * @throws RuntimeException When the rule cannot be saved.
     */
    public function save(array $data): int
    {
        $data = $this->normalizeWritableData($data, true);

        try {
            global $DB;

            $identifier = $DB->insert(self::TABLE, $data);
        } catch (Throwable $exception) {
            $this->log('route.create.failed', ['error' => $exception->getMessage()]);

            throw new RuntimeException('Unable to create the routing rule.', 0, $exception);
        }

        if ($identifier === false) {
            $this->log('route.create.failed', ['error' => 'The database rejected the insert.']);

            throw new RuntimeException('Unable to create the routing rule.');
        }

        $routeId = (int) $identifier;
        $this->log('route.created', ['route_id' => $routeId]);

        return $routeId;
    }

    /**
     * Updates an existing routing rule.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException When the supplied rule is invalid.
     * @throws RuntimeException When the rule cannot be updated.
     */
    public function update(int $routeId, array $data): bool
    {
        $this->assertPositiveId($routeId);
        $data = $this->normalizeWritableData($data, false);

        if ($data === []) {
            return false;
        }

        try {
            global $DB;

            $result = $DB->update(self::TABLE, $data, ['id' => $routeId]);
        } catch (Throwable $exception) {
            $this->log('route.update.failed', [
                'route_id' => $routeId,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Unable to update the routing rule.', 0, $exception);
        }

        if ($result === false) {
            $this->log('route.update.failed', [
                'route_id' => $routeId,
                'error' => 'The database rejected the update.',
            ]);

            throw new RuntimeException('Unable to update the routing rule.');
        }

        $this->log('route.updated', ['route_id' => $routeId]);

        return true;
    }

    /**
     * Deletes a routing rule by identifier.
     *
     * @throws InvalidArgumentException When the identifier is invalid.
     * @throws RuntimeException When the rule cannot be deleted.
     */
    public function delete(int $routeId): bool
    {
        $this->assertPositiveId($routeId);

        try {
            global $DB;

            $result = $DB->delete(self::TABLE, ['id' => $routeId]);
        } catch (Throwable $exception) {
            $this->log('route.delete.failed', [
                'route_id' => $routeId,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Unable to delete the routing rule.', 0, $exception);
        }

        if ($result === false) {
            $this->log('route.delete.failed', [
                'route_id' => $routeId,
                'error' => 'The database rejected the delete.',
            ]);

            throw new RuntimeException('Unable to delete the routing rule.');
        }

        $this->log('route.deleted', ['route_id' => $routeId]);

        return true;
    }

    /**
     * Returns a routing rule by identifier, including inactive rules for administration.
     *
     * @return array<string, mixed>|null
     *
     * @throws InvalidArgumentException When the identifier is invalid.
     * @throws RuntimeException When the rule cannot be read.
     */
    public function getById(int $routeId): ?array
    {
        $this->assertPositiveId($routeId);

        try {
            global $DB;

            foreach ($DB->request([
                'FROM' => self::TABLE,
                'WHERE' => ['id' => $routeId],
                'LIMIT' => 1,
            ]) as $route) {
                return $route;
            }
        } catch (Throwable $exception) {
            $this->log('route.read.failed', [
                'route_id' => $routeId,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Unable to read the routing rule.', 0, $exception);
        }

        return null;
    }

    /**
     * Returns all routing rules for administration, including inactive rules.
     *
     * @return list<array<string, mixed>>
     *
     * @throws RuntimeException When the rules cannot be read.
     */
    public function getAll(): array
    {
        try {
            global $DB;

            $routes = [];
            foreach ($DB->request([
                'FROM' => self::TABLE,
                'ORDER' => ['service ASC', 'branch ASC', 'category ASC', 'id ASC'],
            ]) as $route) {
                $routes[] = $route;
            }

            return $routes;
        } catch (Throwable $exception) {
            $this->log('route.list.failed', ['error' => $exception->getMessage()]);

            throw new RuntimeException('Unable to list routing rules.', 0, $exception);
        }
    }

    /**
     * Resolves duplicate matching rows in a predictable order.
     *
     * @param list<array<string, mixed>> $routes
     *
     * @return array<string, mixed>|null
     */
    private function selectPreferredRoute(array $routes): ?array
    {
        foreach ($routes as $route) {
            if (
                ($route['assignment_mode'] ?? 'FIXED') === 'FIXED'
                && (int) ($route['technician_id'] ?? 0) > 0
            ) {
                return $route;
            }
        }

        foreach ($routes as $route) {
            if (($route['assignment_mode'] ?? 'FIXED') === 'ROUND_ROBIN') {
                return $route;
            }
        }

        return $routes[0] ?? null;
    }

    /**
     * Filters and validates values that may be persisted in the route table.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalizeWritableData(array $data, bool $isCreate): array
    {
        $normalized = array_intersect_key($data, array_flip(self::WRITABLE_FIELDS));

        foreach (['branch', 'service', 'category'] as $field) {
            if (array_key_exists($field, $normalized)) {
                $normalized[$field] = trim((string) $normalized[$field]);
            }
        }

        if ($isCreate) {
            foreach (['branch', 'service', 'category', 'group_id'] as $field) {
                if (!array_key_exists($field, $normalized) || $normalized[$field] === '') {
                    throw new InvalidArgumentException(sprintf('Route field "%s" is required.', $field));
                }
            }
        }

        if (isset($normalized['group_id'])) {
            $normalized['group_id'] = (int) $normalized['group_id'];
            $this->assertPositiveId($normalized['group_id']);
        }

        foreach (['technician_id', 'itilcategories_id', 'priority', 'sla_id', 'entity_id'] as $field) {
            if (array_key_exists($field, $normalized)) {
                $normalized[$field] = $normalized[$field] === '' || $normalized[$field] === null
                    ? null
                    : (int) $normalized[$field];
            }
        }

        if (array_key_exists('assignment_mode', $normalized)) {
            $normalized['assignment_mode'] = strtoupper(trim((string) $normalized['assignment_mode']));
            if (!in_array($normalized['assignment_mode'], ['FIXED', 'ROUND_ROBIN'], true)) {
                throw new InvalidArgumentException('Assignment mode must be FIXED or ROUND_ROBIN.');
            }
        }

        if (array_key_exists('is_active', $normalized)) {
            $normalized['is_active'] = (int) (bool) $normalized['is_active'];
        }

        return $normalized;
    }

    /**
     * Validates a positive database identifier.
     */
    private function assertPositiveId(int $identifier): void
    {
        if ($identifier <= 0) {
            throw new InvalidArgumentException('A positive identifier is required.');
        }
    }

    /**
     * Sends structured repository events to the plugin logger when available.
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
