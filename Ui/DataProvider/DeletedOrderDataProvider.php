<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use CommerceGears\OrderTools\Model\OrderDeleter;

/**
 * Deleted orders log. The keyword search ("Search by keyword") looks for the text in the order number, admin user
 * and customer email; an email address also finds entries where the email was stored as a hash.
 */
class DeletedOrderDataProvider extends DataProvider
{
    private const KEYWORD_COLUMNS = ['main_table.increment_id', 'main_table.customer_email', 'main_table.admin_username'];

    private string $keyword = '';

    /**
     * @inheritDoc
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getConditionType() === 'fulltext') {
            $this->keyword = trim((string)$filter->getValue());
            return;
        }
        parent::addFilter($filter);
    }

    /**
     * @inheritDoc
     */
    public function getSearchResult()
    {
        $result = parent::getSearchResult();
        if ($this->keyword !== '' && $result instanceof AbstractDb) {
            $connection = $result->getConnection();
            $like = '%' . addcslashes($this->keyword, '\\%_') . '%';
            $conditions = [];
            foreach (self::KEYWORD_COLUMNS as $column) {
                $conditions[] = $connection->quoteInto($connection->quoteIdentifier($column) . ' LIKE ?', $like);
            }
            if (str_contains($this->keyword, '@')) {
                $conditions[] = $connection->quoteInto(
                    $connection->quoteIdentifier('main_table.customer_email') . ' = ?',
                    OrderDeleter::hashEmail($this->keyword)
                );
            }
            $result->getSelect()->where(implode(' OR ', $conditions));
        }
        return $result;
    }
}
