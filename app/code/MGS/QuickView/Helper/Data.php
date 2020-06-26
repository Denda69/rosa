<?php

namespace MGS\QuickView\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package MGS\QuickView\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    const XML_PATH_QUICKVIEW_ENABLED = 'mgs_quickview/general/enabled';

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function aroundQuickViewHtml(\Magento\Catalog\Model\Product $product)
    {
        $result = '';
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            $productUrl = $this->_urlBuilder->getUrl('mgs_quickview/catalog_product/view', array('id' => $product->getId()));
            return $result . '<button data-title="' . __("Quick View") . '" class="action mgs-quickview" data-quickview-url=' . $productUrl . ' title="' . __("Quick View") . '"><span class="pe-7s-search"></span></button>';
        }
        return $result;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
