<?php
namespace MGS\Ajaxlayernavigation\Block\Navigation; 


use MGS\Ajaxlayernavigation\Model\Layer\Filter\Item as FilterItem;

class RenderLayered extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Swatches::product/layered/renderer.phtml';

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $eavAttributeModel;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchesHelper;

    /**
     * @var \Magento\Swatches\Helper\Media
     */
    protected $mediaHelper;

    /**
     * RenderLayered constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Eav\Model\Entity\Attribute $eavAttributeModel
     * @param \MGS\Ajaxlayernavigation\Model\ResourceModel\Layer\Filter\AttributeFactory $customAttribute
     * @param \Magento\Swatches\Helper\Data $swatchesHelper
     * @param \Magento\Swatches\Helper\Media $mediaswatchHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Eav\Model\Entity\Attribute $eavAttributeModel,
        \MGS\Ajaxlayernavigation\Model\ResourceModel\Layer\Filter\AttributeFactory $customAttribute,
        \Magento\Swatches\Helper\Data $swatchesHelper,
        \Magento\Swatches\Helper\Media $mediaswatchHelper,
        array $data = []
    ) {
        $this->eavAttributeModel = $eavAttributeModel;
        $this->customAttribute = $customAttribute;
        $this->swatchesHelper = $swatchesHelper;
        $this->mediaHelper = $mediaswatchHelper;

        parent::__construct($context, $data);
    }

    /**
     * @param $filter
     * @return $this
     */
    public function setSwatchFilter($filter)
    {
        $this->filterModel = $filter;
        $this->eavAttributeModel = $filter->getAttributeModel();

        return $this;
    }

    /**
     * @return array
     */
    public function getSwatchData()
    {
        
        $isAttributeModel = $this->eavAttributeModel instanceof \Magento\Eav\Model\Entity\Attribute;
        if (false === $isAttributeModel) {
            throw new \RuntimeException('Attribute model has not been set.');
        }

        $attrOptions = [];
        foreach ($this->eavAttributeModel->getOptions() as $option) {
            if ($current = $this->getCurrentOption($this->filterModel->getItems(), $option)) {
                $attrOptions[$option->getValue()] = $current;
            } elseif ($this->isShowEmpty()) {
                $attrOptions[$option->getValue()] = $this->getIsUnused($option);
            }
        }

        $optionIds = array_keys($attrOptions);
        $swatchesData = $this->swatchesHelper->getSwatchesByOptionsId($optionIds);

        $data = [
            'attribute_id' => $this->eavAttributeModel->getId(),
            'attribute_code' => $this->eavAttributeModel->getAttributeCode(),
            'attribute_label' => $this->eavAttributeModel->getStoreLabel(),
            'options' => $attrOptions,
            'swatches' => $swatchesData,
        ];

        return $data;
    }

    /**
     * @param $code
     * @param $id
     * @return string
     */
    public function buildUrl($code, $id)
    {
        return $this->_urlBuilder->getUrl(
            '*/*/*',
            [
                '_current' => true,
                '_use_rewrite' => true,
                '_query' => [$code => $id]
            ]
        );
    }

    /**
     * @param Option $option
     * @return array
     */
    protected function getIsUnused(Option $option)
    {
        return [
            'label' => $option->getLabel(),
            'link' => 'javascript:void();',
            'custom_style' => 'disabled'
        ];
    }

    /**
     * @param array $items
     * @param $option
     * @return array|bool
     */
    protected function getCurrentOption(array $items, $option)
    {
        $result = false;
        $item = $this->getFilterItemById($items, $option->getValue());
        if ($item) {
            $result = $this->getViewData($item, $option);
        }

        return $result;
    }

    /**
     * @param $item
     * @param $option
     * @return array
     */
    protected function getViewData($item,$option)
    {
        $custom = '';
        $code = $this->eavAttributeModel->getAttributeCode();
        $value = $item->getValue();
        $optionLink = $item->getUrl();
        if ($this->isDisabled($item)) {
            $custom = 'disabled';
            $optionLink = $item->getRemoveUrl();
        }

        return [
            'label' => $option->getLabel(),
            'link' => $optionLink,
            'custom_style' => $custom
        ];
    }

    /**
     * @param $filterItem
     * @return bool
     */
    protected function isOptionVisible($filterItem)
    {
        return $this->isDisabled($filterItem) && $this->isShowEmpty() ? false : true;
    }

    /**
     * @return bool
     */
    protected function isShowEmpty()
    {
        return $this->eavAttributeModel->getIsFilterable() != 1;
    }

    /**
     * @param $item
     * @return bool
     */
    protected function isDisabled($item)
    {
        return !$item->getCount();
    }

    /**
     * @param array $items
     * @param $id
     * @return bool|mixed
     */
    protected function getFilterItemById(array $items, $id)
    {
        foreach ($items as $item) {
            if ($id == $item->getValue()) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param $type
     * @param $file
     * @return string
     */
    public function getSwatchPath($type, $file)
    {
        return $this->mediaHelper->getSwatchAttributeImage($type, $file);
    }
}
