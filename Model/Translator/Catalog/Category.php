<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Model\Translator\Catalog;

use Aromicon\Deepl\Api\TranslatorInterface;
use Aromicon\Deepl\Helper\Config;
use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\Exception\LocalizedException;

class Category
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Config
     */
    private $config;

    private $categoryResource;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        TranslatorInterface $translator,
        Config $config,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->translator = $translator;
        $this->config = $config;
        $this->categoryResource = $categoryResource;
    }

    /**
     * @param $productId int
     * @param $fromStoreId int
     * @param $toStoreId int
     * @throws LocalizedException
     */
    public function translateAndCopy($categoryId, $toStoreId)
    {
        $sourceCategory = $this->categoryRepository->get($categoryId, $this->config->getSourceStoreId($toStoreId));
        $category = $this->categoryRepository->get($categoryId, $toStoreId);

        $sourceLanguage = $this->config->getSourceLanguage($toStoreId);
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId, true);

        $categoryFields = $this->config->getTranslatableCategoryFields();

        foreach ($categoryFields as $field) {
            if ($sourceCategory->getData($field) == '') {
                continue;
            }

            $translatedText = $this->translator
                ->translate($sourceCategory->getData($field), $sourceLanguage, $targetLanguage);

            if ($category->getData($field) == $translatedText) {
                continue;
            }

            $category->setData($field, $translatedText);
            $this->saveAttribute($category, $field);
        }
    }

    /**
     * @throws Exception
     */
    public function saveAttribute(CategoryInterface $category, string $field): AbstractEntity
    {
        return $this->categoryResource->saveAttribute($category, $field);
    }
}
