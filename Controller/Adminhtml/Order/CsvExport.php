<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Oander\FoxPostShippingMethod\Enum\Attributes;
use Oander\FoxPostShippingMethod\Helper\ParcelInformationFactory;

class CsvExport extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions';

    const PACKAGE_SIZE = "M";

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var ParcelInformationFactory
     */
    protected ParcelInformationFactory $parcelInformationHelper;

    /**
     * @param Context                  $context
     * @param Filter                   $filter
     * @param CollectionFactory        $collectionFactory
     * @param DateTime                 $dateTime
     * @param FileFactory              $fileFactory
     * @param Filesystem               $filesystem
     * @param ParcelInformationFactory $parcelInformationHelper
     */
    public function __construct(
        Context                  $context,
        Filter                   $filter,
        CollectionFactory        $collectionFactory,
        DateTime                 $dateTime,
        FileFactory              $fileFactory,
        Filesystem               $filesystem,
        ParcelInformationFactory $parcelInformationHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->parcelInformationHelper = $parcelInformationHelper;
        parent::__construct($context, $filter);
    }

    /**
     * @param  AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        try {
            $fileName = sprintf('foxpost_export%s.csv', $this->dateTime->date('Y-m-d_H-i-s'));
            $filePath = sprintf('export/%s', $fileName);
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $directory->create('export');
            $stream = $directory->openFile($filePath, 'w+');
            $stream->lock();

            $header = $this->getColumnHeader();
            $stream->writeCsv(
                $header, ','
            );

            foreach ($collection as $order) {
                $stream->writeCsv(
                    $this->getRow($order),
                    ','
                );
            }

            $stream->unlock();

            $fileContent = ['type' => 'string', 'value' => $stream->readAll(), 'rm' => true];
            return $this->fileFactory->create(
                $fileName,
                $fileContent,
                DirectoryList::VAR_DIR,
                'text/csv'
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('sales/order/');
        }
    }

    /**
     * Get CSV column header
     *
     * @return array
     */
    protected function getColumnHeader(): array
    {
        return [
            __("Addressee name"),
            __("Addressee telephone"),
            __("Addressee email"),
            __("Addressee machine"),
            __("City"),
            __("Postcode"),
            __("Street"),
            __("Grand total"),
            __("Package size"),
            __("Courier details"),
            __("Personal details"),
            __("Label print"),
            __("Fragile"),
            __("Custom bar code"),
            __("Reference code")
        ];
    }

    /**
     * @param  $order
     * @return array|bool
     * @throws LocalizedException
     */
    protected function getRow($order)
    {

        $shippingMethod = $order->getShippingMethod();
        $parcelInformation = $this->parcelInformationHelper->create();
        $parcelInformation->setOrderId((int)$order->getId());

        return ($shippingMethod === Attributes::FOX_POST_SHIPPING_METHOD_CODE)
            ? [
                $order->getCustomerName(),
                $parcelInformation->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_PHONENUMBER),
                $order->getCustomerEmail(),
                $parcelInformation->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_CODE),
                '',
                '',
                '',
                '',
                self::PACKAGE_SIZE,
                '',
                '',
                '',
                '',
                '',
                ''
            ] : false;
    }
}
