<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use CommerceGears\OrderTools\Model\Numbering\Formatter;

/**
 * Validates a number prefix or suffix: letters, digits, "-", "_", "." and the placeholders only.
 */
class Affix extends Value
{
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly Formatter $formatter,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $value = trim((string)$this->getValue());
        if (!$this->formatter->isValidAffix($value)) {
            $entity = explode('/', (string)$this->getPath())[1] ?? '';
            $labels = [
                'order' => __('Order Numbers'),
                'invoice' => __('Invoice Numbers'),
                'shipment' => __('Shipment Numbers'),
                'creditmemo' => __('Credit Memo Numbers'),
            ];
            throw new LocalizedException(__(
                '%1: the %2 is not valid. Use up to %3 characters: letters, digits, "-", "_", "." and the placeholders %4.',
                $labels[$entity] ?? '',
                $this->getField() === 'suffix' ? __('suffix') : __('prefix'),
                Formatter::MAX_AFFIX_LENGTH,
                implode(' ', Formatter::PLACEHOLDERS)
            ));
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
