<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Ui\Component;

use Magento\Ui\Component\ExportButton;

/**
 * Export button of the deleted orders log: CSV through the module's own (formula-safe) export only.
 */
class CsvExportButton extends ExportButton
{
    public function prepare()
    {
        $config = (array)$this->getData('config');
        $config['options'] = [
            'csv' => [
                'value' => 'csv',
                'label' => __('CSV'),
                'url' => 'commercegears_ordertools/deleted/exportCsv',
            ],
        ];
        $this->setData('config', $config);
        parent::prepare();
    }
}
