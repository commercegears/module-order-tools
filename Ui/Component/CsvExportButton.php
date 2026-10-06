<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Ui\Component;

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
                'url' => 'softaware_ordertools/deleted/exportCsv',
            ],
        ];
        $this->setData('config', $config);
        parent::prepare();
    }
}
