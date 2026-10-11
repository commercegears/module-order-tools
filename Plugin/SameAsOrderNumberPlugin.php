<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Plugin;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use CommerceGears\OrderTools\Model\Config;
use CommerceGears\OrderTools\Model\Numbering\NumberGenerator;

/**
 * "Same as the order number" for invoices, shipments and credit memos: sets the number before Magento's resource
 * model would take one from the sequence (it only does so when the document has no number yet).
 */
class SameAsOrderNumberPlugin
{
    public function __construct(
        private readonly Config $config,
        private readonly NumberGenerator $generator
    ) {
    }

    /**
     * @param EntityAbstract $subject
     * @param AbstractModel $object
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(EntityAbstract $subject, AbstractModel $object)
    {
        if ($object->getId() || $object->getData('increment_id')) {
            return null;
        }
        $entityType = method_exists($object, 'getEntityType') ? (string)$object->getEntityType() : '';
        if (!in_array($entityType, ['invoice', 'shipment', 'creditmemo'], true)) {
            return null;
        }
        $order = $object->getOrder();
        if (!$order || !$order->getIncrementId()) {
            return null;
        }
        $settings = $this->config->getNumberSettings($entityType, (int)$object->getStoreId());
        if (!$settings->isSameAsOrder()) {
            return null;
        }
        $number = $this->generator->sameAsOrder($entityType, (string)$order->getIncrementId());
        if ($number !== null) {
            $object->setIncrementId($number);
        }
        return null;
    }
}
