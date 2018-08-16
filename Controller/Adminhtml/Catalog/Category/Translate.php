<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Controller\Adminhtml\Catalog\Category;

use Magento\Framework\Exception\LocalizedException;

class Translate extends \Aromicon\Deepl\Controller\Adminhtml\Catalog
{

    /**
     * @var \Aromicon\Deepl\Model\Translator\Catalog\Category
     */
    private $categoryTranslator;

    /**
     * Translate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aromicon\Deepl\Model\Translator\Catalog\Category $categoryTranslator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aromicon\Deepl\Model\Translator\Catalog\Category $categoryTranslator
    ) {
        $this->categoryTranslator = $categoryTranslator;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $store = $this->getRequest()->getParam('store');

        try {
            $this->categoryTranslator->translateAndCopy($categoryId, $store);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Category couldn\'t be translated. %1', $e->getMessage()));
        }

        $this->_redirect('catalog/category/edit', ['id' => $categoryId]);
    }
}