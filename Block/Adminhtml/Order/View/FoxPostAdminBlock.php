<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\StoreManagerInterface;
use Oander\FoxPostShippingMethod\Enum\Attributes;
use Oander\FoxPostShippingMethod\Helper\Config;
use Oander\FoxPostShippingMethod\Helper\ParcelInformation;

class FoxPostAdminBlock extends Template
{
    /**
     * @var ParcelInformation
     */
    protected ParcelInformation $parcelInformationHelper;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var Config
     */
    private Config $configHelper;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Context               $context
     * @param array                 $data
     * @param JsonHelper|null       $jsonHelper
     * @param DirectoryHelper|null  $directoryHelper
     * @param ParcelInformation     $parcelInformationHelper
     * @param Filesystem            $filesystem
     * @param DirectoryList         $directoryList
     * @param Config                $configHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Template\Context      $context,
        array                 $data = [],
        ?JsonHelper           $jsonHelper = null,
        ?DirectoryHelper      $directoryHelper = null,
        ParcelInformation     $parcelInformationHelper,
        Filesystem            $filesystem,
        DirectoryList         $directoryList,
        Config                $configHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->parcelInformationHelper = $parcelInformationHelper;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->parcelInformationHelper->setOrderId((int)$this->getRequest()->getParam('order_id', 0));
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getMachineName(): string
    {
        return $this->parcelInformationHelper->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_NAME);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getMachineCode(): string
    {
        return $this->parcelInformationHelper->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_CODE);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getMachineAddress(): string
    {
        return $this->parcelInformationHelper->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_ADDRESS);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPhoneNumber(): string
    {
        return $this->parcelInformationHelper->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_PHONENUMBER);
    }

    /**
     * @return float|DataObject|string|null
     * @throws LocalizedException
     */
    public function getShippingMethod()
    {
        return $this->parcelInformationHelper->getShippingMethod();
    }

    /**
     * Gets the PDF file based on the order ID and passes it to the view.
     *
     * @return string|void
     * @throws NoSuchEntityException
     */
    public function getLabelFilePath()
    {
        $filename = $this->configHelper->getFoxpostLabelFolder() . '/' . $this->getRequest()->getParam('order_id', 0) . '.pdf';
        if (file_exists($this->filesystem->getDirectoryRead($this->directoryList::MEDIA)->getAbsolutePath() . '/' . $filename)) {
            return $this->storeManager->getStore()->getBaseUrl() . $this->directoryList::MEDIA . '/' . $filename;
        }
    }
}
