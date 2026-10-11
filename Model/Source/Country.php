<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Source;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Countries for the Shipping Country grid column (all countries, not only the allowed ones, so older orders still
 * show a name).
 */
class Country implements OptionSourceInterface
{
    private ?array $options = null;

    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $options = $this->collectionFactory->create()->toOptionArray(false);
            $this->options = array_values(array_filter($options, static fn ($o) => (string)$o['value'] !== ''));
        }
        return $this->options;
    }
}
