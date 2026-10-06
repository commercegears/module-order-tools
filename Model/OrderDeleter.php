<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Softaware\OrderTools\Model\ResourceModel\DeletedOrder as DeletedOrderResource;

/**
 * Deletes orders with all their documents, guarded by the settings, and writes the deleted orders log.
 *
 * The guards are checked here (server side) for every order, whatever the caller showed in the admin:
 * the feature must be on, at most MAX_BATCH orders per call, and each order's current status must be one of the
 * allowed statuses. Every order is deleted in its own transaction together with its log entry.
 */
class OrderDeleter
{
    public const MAX_BATCH = 200;

    public function __construct(
        private readonly Config $config,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly ShipmentRepositoryInterface $shipmentRepository,
        private readonly CreditmemoRepositoryInterface $creditmemoRepository,
        private readonly ResourceConnection $resource,
        private readonly Registry $registry,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int[] $orderIds
     * @param array{user_id: ?int, username: ?string, ip: ?string} $actor
     * @return array{deleted: string[], refused: array<string, string>, failed: array<string, string>}
     * @throws LocalizedException when deleting is switched off or too many orders were selected
     */
    public function delete(array $orderIds, array $actor): array
    {
        if (!$this->config->isDeleteEnabled()) {
            throw new LocalizedException(
                __('Deleting orders is switched off in Stores > Configuration > Softaware > Order Tools.')
            );
        }
        $orderIds = array_values(array_unique(array_filter(array_map('intval', $orderIds), fn ($id) => $id > 0)));
        if (!$orderIds) {
            throw new LocalizedException(__('Select at least one order.'));
        }
        if (count($orderIds) > self::MAX_BATCH) {
            throw new LocalizedException(
                __('You selected %1 orders. Delete at most %2 orders at a time.', count($orderIds), self::MAX_BATCH)
            );
        }

        $result = ['deleted' => [], 'refused' => [], 'failed' => []];
        $secureAreaSet = false;
        if (!$this->registry->registry('isSecureArea')) {
            $this->registry->register('isSecureArea', true);
            $secureAreaSet = true;
        }
        try {
            foreach ($orderIds as $orderId) {
                try {
                    $order = $this->orderRepository->get($orderId);
                } catch (NoSuchEntityException $e) {
                    $result['failed']['ID ' . $orderId] = (string)__('Order not found.');
                    continue;
                } catch (\Throwable $e) {
                    $this->logger->error('Softaware_OrderTools: loading order ' . $orderId . ' failed: ' . $e->getMessage());
                    $result['failed']['ID ' . $orderId] = (string)__('The order could not be loaded. See var/log/system.log.');
                    continue;
                }
                $label = '#' . $order->getIncrementId();
                if (!$this->config->isStatusDeletable((string)$order->getStatus())) {
                    $result['refused'][$label] = (string)$order->getStatus();
                    continue;
                }
                try {
                    $this->deleteOne($order, $actor);
                    $result['deleted'][] = $label;
                } catch (\Throwable $e) {
                    $this->logger->error('Softaware_OrderTools: deleting order ' . $orderId . ' failed: ' . $e->getMessage());
                    $result['failed'][$label] = $e instanceof LocalizedException
                        ? $e->getMessage()
                        : (string)__('Database error, nothing was deleted. See var/log/system.log.');
                }
            }
        } finally {
            if ($secureAreaSet) {
                $this->registry->unregister('isSecureArea');
            }
        }
        return $result;
    }

    /**
     * @param OrderInterface&Order $order
     */
    private function deleteOne(OrderInterface $order, array $actor): void
    {
        $orderId = (int)$order->getEntityId();
        $connection = $this->resource->getConnection('sales');
        $connection->beginTransaction();
        try {
            // Lock the order row and check its status again: it may have changed (or the order may have been
            // deleted by someone else) since it was loaded.
            $status = $connection->fetchOne(
                $connection->select()
                    ->from($this->resource->getTableName('sales_order'), ['status'])
                    ->where('entity_id = ?', $orderId)
                    ->forUpdate(true)
            );
            if ($status === false) {
                throw new LocalizedException(__('The order no longer exists.'));
            }
            if (!$this->config->isStatusDeletable((string)$status)) {
                throw new LocalizedException(__('The order status changed to "%1", which may not be deleted.', $status));
            }
            $documents = [];
            $deleted = [];
            foreach ($order->getCreditmemosCollection() ?: [] as $creditmemo) {
                $deleted['creditmemo'][] = $creditmemo->getIncrementId();
                $this->creditmemoRepository->delete($creditmemo);
            }
            foreach ($order->getShipmentsCollection() ?: [] as $shipment) {
                $deleted['shipment'][] = $shipment->getIncrementId();
                $this->shipmentRepository->delete($shipment);
            }
            foreach ($order->getInvoiceCollection() ?: [] as $invoice) {
                $deleted['invoice'][] = $invoice->getIncrementId();
                $this->invoiceRepository->delete($invoice);
            }
            foreach (['invoice' => 'Invoices', 'shipment' => 'Shipments', 'creditmemo' => 'Credit memos'] as $k => $l) {
                if (!empty($deleted[$k])) {
                    $documents[] = $l . ': ' . implode(' ', $deleted[$k]);
                }
            }

            // Tax summary rows have no foreign key to the order (their item rows cascade from them).
            $taxTable = $this->resource->getTableName('sales_order_tax');
            if ($connection->isTableExists($taxTable)) {
                $connection->delete($taxTable, ['order_id = ?' => $orderId]);
            }

            $logEntry = $this->buildLogEntry($order, $actor, $documents);
            // Items, addresses, payment, transactions and comments are removed by the foreign keys; the order grid
            // row by Magento's sales_order_delete_after observer.
            $this->orderRepository->delete($order);
            $connection->insert($this->resource->getTableName(DeletedOrderResource::TABLE), $logEntry);
            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param OrderInterface&Order $order
     * @param string[] $documents
     */
    private function buildLogEntry(OrderInterface $order, array $actor, array $documents): array
    {
        $email = trim((string)$order->getCustomerEmail());
        if ($email !== '' && $this->config->getEmailInLog() === Config::EMAIL_HASH) {
            $email = 'sha256:' . hash('sha256', mb_strtolower($email));
        }
        return [
            'order_id' => (int)$order->getEntityId(),
            'increment_id' => (string)$order->getIncrementId(),
            'store_id' => $order->getStoreId() !== null ? (int)$order->getStoreId() : null,
            'status' => (string)$order->getStatus(),
            'state' => (string)$order->getState(),
            'grand_total' => $order->getGrandTotal(),
            'order_currency_code' => (string)$order->getOrderCurrencyCode(),
            'customer_email' => $email !== '' ? mb_substr($email, 0, 255) : null,
            'is_test' => (int)$order->getData('sot_is_test') ? 1 : 0,
            'documents' => $documents ? mb_substr(implode('; ', $documents), 0, 255) : null,
            'order_created_at' => $order->getCreatedAt(),
            'admin_user_id' => $actor['user_id'] ?? null,
            'admin_username' => isset($actor['username']) ? mb_substr((string)$actor['username'], 0, 40) : null,
            'ip' => isset($actor['ip']) ? mb_substr((string)$actor['ip'], 0, 45) : null,
        ];
    }
}
