<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2017 aromicon GmbH (http://www.aromicon.de)
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 */
namespace Aromicon\Deepl\Helper;

use Aromicon\Deepl\Model\System\Config\CategoryFields;
use Aromicon\Deepl\Model\System\Config\PageFields;
use Aromicon\Deepl\Model\System\Config\ProductFields;
use Aromicon\Deepl\Model\System\Config\Version;
use Locale;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Bundle\LanguageBundle;
use Magento\Framework\Locale\Bundle\RegionBundle;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH_DEFAULT_LOCALE = 'general/locale/code';
    const XML_PATH_DEFAULT_STORE = 'deepl/general/store';
    const XML_PATH_DEFAULT_SPLIT_SENTENCE = 'deepl/api/split_sentence';
    const XML_PATH_DEFAULT_FORMALITY = 'deepl/api/formality';
    const XML_PATH_DEEPL_API_URL = 'deepl/api/url';
    const XML_PATH_DEEPL_API_URL_FREE = 'deepl/api/url_free';
    const XML_PATH_DEEPL_API_VERSION = 'deepl/api/version';
    const XML_PATH_DEEPL_API_KEY = 'deepl/api/key';
    const XML_PATH_DEEPL_CMS_PAGE_FIELDS = 'deepl/cms/page_fields';
    const XML_PATH_DEEPL_PRODUCT_FIELDS = 'deepl/product/product_fields';
    const XML_PATH_DEEPL_CATEGORY_FIELDS = 'deepl/category/category_fields';
    const XML_PATH_DEEPL_LOG_ENABLE = 'deepl/log/enable_log';
    const XML_PATH_DEEPL_TIMEOUT = 'deepl/api/timeout';
    const XML_PATH_DEEPL_TAG_HANDLING = 'deepl/api/tag_handling';
    const XML_PATH_DEEPL_IGNORE_TAG = 'deepl/api/ignore_tags';

    /**
     * @var PageFields
     */
    private $pageFields;

    /**
     * @var ProductFields
     */
    private $productFields;

    /**
     * @var ProductFields
     */
    private $categoryFields;

    public function __construct(
        Context $context,
        PageFields $pageFields,
        ProductFields $productFields,
        CategoryFields $categoryFields
    ) {
        $this->pageFields = $pageFields;
        $this->productFields = $productFields;
        $this->categoryFields = $categoryFields;
        parent::__construct($context);
    }

    /**
     * Get Battery Deposit is included in Product Price
     * @return bool
     */
    public function getStoreLanguage($store = null)
    {
        $locale = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_LOCALE,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        $currentLocale = 'en';
        $languages = (new LanguageBundle())->get($currentLocale)['Languages'];

        $language = Locale::getPrimaryLanguage($locale);

        return  $languages[$language];
    }

    /**
     * @param $storeId
     * @return mixed|null|string|string[]
     */
    public function getLanguageCodeByStoreId($storeId, $isTarget = false)
    {
        $language = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_LOCALE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($isTarget) {
            if ($language == 'en_GB') {
                return 'EN-GB';
            } elseif(strpos($language, 'en') === 0) {
                return 'EN-US';
            }

            //Switch for Portuguese
            if ($language == 'pt_BR') {
                return 'PT-BR';
            } elseif(strpos($language, 'pt') === 0) {
                return 'PT-PT';
            }
        }

        return mb_strtoupper(mb_substr($language, 0, 2));
    }

    /**
     * @return mixed|null|string|string[]
     */
    public function getSourceStoreId($storeId = null)
    {
        $storeId = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_STORE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $storeId;
    }

    /**
     * @return mixed|null|string|string[]
     */
    public function getSourceLanguage($storeId = null)
    {
        $storeId = $this->getSourceStoreId($storeId);
        return $this->getLanguageCodeByStoreId($storeId);
    }

    /**
     * @return mixed
     */
    public function getDeeplApiUrl()
    {
        if ($this->getDeeplApiVersion() == Version::FREE) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_DEEPL_API_URL_FREE
            );
        }

        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_API_URL
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDeeplApiVersion($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_API_VERSION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function hasApiKey($storeId = null)
    {
        return !empty($this->getDeeplApiKey($storeId));
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDeeplApiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array|mixed
     */
    public function getTranslatablePageFields()
    {
        $fields = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_CMS_PAGE_FIELDS,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($fields)) {
            $fields = [];
            foreach ($this->pageFields->toOptionArray() as $item) {
                $fields[] = $item['value'];
            }
        } else {
            $fields = explode(',', $fields);
        }

        return $fields;
    }

    /**
     * @return array|mixed
     */
    public function getTranslatableProductFields()
    {
        $fields = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_PRODUCT_FIELDS,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($fields)) {
            $fields = [];
            foreach ($this->pageFields->toOptionArray() as $item) {
                $fields[] = $item['value'];
            }
        } else {
            $fields = explode(',', $fields);
        }

        return $fields;
    }

    /**
     * @return array|mixed
     */
    public function getTranslatableCategoryFields()
    {
        $fields = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_CATEGORY_FIELDS,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($fields)) {
            $fields = [];
            foreach ($this->pageFields->toOptionArray() as $item) {
                $fields[] = $item['value'];
            }
        } else {
            $fields = explode(',', $fields);
        }

        return $fields;
    }

    /**
     * @return mixed
     */
    public function isLogEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_LOG_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getSplitSentences()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_SPLIT_SENTENCE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getFormality()
    {
        $formality = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_FORMALITY,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($formality)) {
            $formality = 'default';
        }

        return $formality;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_TIMEOUT,
            ScopeInterface::SCOPE_STORE
        );
        return $value > 0 ? $value : 30;
    }

    /**
     * @return string
     */
    public function getTagHandling()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_TAG_HANDLING,
            ScopeInterface::SCOPE_STORE
        );

        return !empty($value) ? $value : 'xml';
    }

    public function getIgnoreTag(): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_IGNORE_TAG,
            ScopeInterface::SCOPE_STORE
        );

        return !empty($value) ? $value : 'x';
    }
}
