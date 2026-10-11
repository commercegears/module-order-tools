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
use CommerceGears\OrderTools\Model\Numbering\Settings;

/**
 * A counter may only restart when the number contains the period (year, or year and month); otherwise the restarted
 * counter would repeat earlier numbers.
 */
class CounterReset extends Value
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
        $reset = (string)$this->getValue();
        if ($reset !== Settings::RESET_NEVER && (string)$this->getFieldsetDataValue('mode') === 'custom') {
            $prefix = (string)$this->getFieldsetDataValue('prefix');
            $suffix = (string)$this->getFieldsetDataValue('suffix');
            if (!$this->formatter->canReset($reset, $prefix, $suffix)) {
                throw new LocalizedException(
                    $reset === Settings::RESET_MONTHLY
                        ? __('A monthly counter reset needs {yyyy} or {yy} and {mm} in the prefix or suffix, otherwise numbers would repeat.')
                        : __('A yearly counter reset needs {yyyy} or {yy} in the prefix or suffix, otherwise numbers would repeat.')
                );
            }
        }
        return parent::beforeSave();
    }
}
