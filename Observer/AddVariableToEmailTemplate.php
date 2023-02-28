<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Oander\FoxPostShippingMethod\Enum\Attributes;
use Oander\FoxPostShippingMethod\Helper\ParcelInformationFactory;

class AddVariableToEmailTemplate implements ObserverInterface
{
    /**
     * @var ParcelInformationFactory
     */
    private ParcelInformationFactory $parcelInformationFactoryHelper;

    /**
     * @param ParcelInformationFactory $parcelInformationFactoryHelper
     */
    public function __construct(
        ParcelInformationFactory $parcelInformationFactoryHelper
    ) {
        $this->parcelInformationFactoryHelper = $parcelInformationFactoryHelper;
    }

    /**
     * @param  Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $transport = $observer->getTransport();
        $order = $transport->getOrder();

        if ($order->getShippingMethod() == Attributes::FOX_POST_SHIPPING_METHOD_CODE) {
            $parcelInformation = $this->parcelInformationFactoryHelper->create();
            $parcelInformation->setOrderId((int)$order->getId());
            if (null == $transport[Attributes::EMAIL_VARIABLE]) {
                $transport[Attributes::EMAIL_VARIABLE] = trim($parcelInformation->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_ADDRESS) . ' (' . $parcelInformation->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_CODE) . ') ' . $parcelInformation->getFoxPostItemData(Attributes::JSON_ATTRIBUTE_PHONENUMBER));
            }
        }

    }
}
