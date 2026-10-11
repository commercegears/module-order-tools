<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Deleted orders log: a hashed customer email (sha256:<64 hex>) is shown shortened so it does not stretch the grid.
 * The CSV export and the keyword search use the full value.
 */
class CustomerEmail extends Column
{
    private const HASH_PREFIX = 'sha256:';

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $name = (string)$this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $i => $item) {
            $value = (string)($item[$name] ?? '');
            if (str_starts_with($value, self::HASH_PREFIX)) {
                $dataSource['data']['items'][$i][$name] = (string)__(
                    'Hashed (%1…)',
                    substr($value, strlen(self::HASH_PREFIX), 10)
                );
            }
        }
        return $dataSource;
    }
}
