<?php

namespace MGS\Ajaxlayernavigation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Search extends AbstractHelper
{
    /**
     * @var \Magento\Search\Api\SearchInterface
     */
    private $_objectManager;

    /**
     * Search constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Search\Api\SearchInterface $search
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Search\Api\SearchInterface $search,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) { 
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function search()
    {
        $searchQuery = $this->_request->getParam('q');
        if (!$searchQuery) {
            return false;
        }

        $search = $this->_objectManager->create('\Magento\Search\Api\SearchInterface');
        $searchCriteriaBuilder = $this->_objectManager->create('\Magento\Framework\Api\Search\SearchCriteriaBuilder');
        $filterBuilder = $this->_objectManager->create('\Magento\Framework\Api\FilterBuilder');

        $filterBuilder->setField('search_term')->setValue($searchQuery);
        $searchCriteriaBuilder->addFilter($filterBuilder->create());
        $searchCriteria = $searchCriteriaBuilder->create();

        $searchCriteria->setRequestName('quick_search_container');
        $items = $search->search($searchCriteria)->getItems();
        if (count($items) > 0) {
            $entityIds = [];
            foreach ($items as $item) {
                $entityIds[] = $item->getId();
            }

            return $entityIds;
        }
        return false;
    }
}
