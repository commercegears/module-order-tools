<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\Numbering;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\ConfigInterface as ResourceConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;

/**
 * Atomic document counters (table softaware_ordertools_counter).
 *
 * The increment is one INSERT ... ON DUPLICATE KEY UPDATE statement, so two requests can never get the same value.
 * It runs on its own database connection (auto-commit): sales documents are saved inside transactions, and taking
 * the next value there would keep the counter row locked until the whole order/invoice save has finished,
 * serialising checkouts. Like Magento's own sequence tables, a value taken by a request that fails later is not
 * reused (the number is skipped).
 */
class Counter
{
    public const TABLE = 'softaware_ordertools_counter';

    private ?AdapterInterface $connection = null;

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly ResourceConfig $resourceConfig,
        private readonly DeploymentConfig $deploymentConfig,
        private readonly ConnectionFactory $connectionFactory
    ) {
    }

    /**
     * Takes the next value: the start value for a new counter, otherwise the last value plus the step. If the start
     * value was raised above the counter, the counter jumps to it; lowering it has no effect on a running counter.
     */
    public function next(string $entityType, string $pattern, string $period, int $start, int $step): int
    {
        $connection = $this->getConnection();
        $table = $this->resource->getTableName(self::TABLE);
        $connection->query(
            'INSERT INTO ' . $connection->quoteIdentifier($table)
            . ' (`entity_type`, `pattern`, `period`, `counter_value`)'
            . ' VALUES (?, ?, ?, LAST_INSERT_ID(CAST(? AS UNSIGNED)))'
            . ' ON DUPLICATE KEY UPDATE `counter_value` ='
            . ' LAST_INSERT_ID(GREATEST(`counter_value` + CAST(? AS UNSIGNED), CAST(? AS UNSIGNED)))',
            [$entityType, $pattern, $period, $start, $step, $start]
        );
        return (int)$connection->fetchOne('SELECT LAST_INSERT_ID()');
    }

    /**
     * The value next() would return now, without taking it (for previews).
     */
    public function peek(string $entityType, string $pattern, string $period, int $start, int $step): int
    {
        $connection = $this->resource->getConnection('sales');
        $last = $connection->fetchOne(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE), ['counter_value'])
                ->where('entity_type = ?', $entityType)
                ->where('pattern = ?', $pattern)
                ->where('period = ?', $period)
        );
        return $last === false ? $start : max((int)$last + $step, $start);
    }

    /**
     * Separate connection with the same settings as the sales connection, opened on first use.
     */
    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $name = $this->resourceConfig->getConnectionName('sales') ?: ResourceConnection::DEFAULT_CONNECTION;
            $config = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/' . $name)
                ?: $this->deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/' . ResourceConnection::DEFAULT_CONNECTION
                );
            $this->connection = is_array($config)
                ? $this->connectionFactory->create($config)
                : $this->resource->getConnection('sales');
        }
        return $this->connection;
    }
}
