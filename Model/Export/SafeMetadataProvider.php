<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Ui\Model\Export\MetadataProvider;

/**
 * Grid CSV export that neutralises spreadsheet formulas (values starting with = + - @ tab or carriage return).
 */
class SafeMetadataProvider extends MetadataProvider
{
    /**
     * @inheritDoc
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        return array_map(
            static function ($value) {
                if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                    return "'" . $value;
                }
                return $value;
            },
            parent::getRowData($document, $fields, $options)
        );
    }
}
