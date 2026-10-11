<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Plugin;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\SalesSequence\Model\Manager;
use CommerceGears\OrderTools\Model\Config;
use CommerceGears\OrderTools\Model\Numbering\CustomSequenceFactory;

/**
 * Hands Magento a custom sequence for order/invoice/shipment/credit memo numbers where the store view uses a
 * custom format. Everything else (other entity types, "Magento default") keeps Magento's sequence.
 */
class SequenceManagerPlugin
{
    public function __construct(
        private readonly Config $config,
        private readonly CustomSequenceFactory $customSequenceFactory
    ) {
    }

    /**
     * @param Manager $subject
     * @param SequenceInterface $result
     * @param string $entityType
     * @param int|string $storeId
     * @return SequenceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSequence(Manager $subject, $result, $entityType, $storeId)
    {
        if (!$result instanceof SequenceInterface
            || !in_array((string)$entityType, Config::ENTITY_TYPES, true)
        ) {
            return $result;
        }
        $settings = $this->config->getNumberSettings((string)$entityType, (int)$storeId);
        if (!$settings->isCustom()) {
            return $result;
        }
        return $this->customSequenceFactory->create(['settings' => $settings, 'fallback' => $result]);
    }
}
